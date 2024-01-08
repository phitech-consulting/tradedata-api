<?php

namespace App\Classes;

use App\Exceptions\QuoteRetrieveException;
use App\Exceptions\QuoteStoreException;
use App\Jobs\StoreOneQuote;
use App\Models\IexHistoricSymbolSetModel;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Classes\StockQuote;

class IexApi
{
    public $environment = [];


    public function __construct() {
        $env = env('iex_environment', false); // ['_sandbox', '_production']
        $this->environment = [
            'environment' => $env,
            'api_url' => env('iex_cloud_host' . $env, false),
            'api_token' => env('iex_cloud_token' . $env, false),
            'http_source_ref' => env('iex_http_source' . $env, false),
        ];
    }


    /**
     * Get quote for one single symbol. Automatically decide which IEX endpoint to use (today or historic).
     * @param $symbol
     * @param $date
     * @return StockQuote|void
     * @throws QuoteRetrieveException
     */
    public function get_quote($symbol, $date = null) {

        // If date is given, convert to YYYY-MM-DD format, otherwise set to null.
        $date = $date ? date('Y-m-d', strtotime($date)) : null;

        // Set today's date in YYYY-MM-DD format.
        $now = date("Y-m-d", strtotime(now()));

        // If date is not given, set to today's date.
        $date = $date ?? $now;

        // Return quote based on date.
        if($date == $now) {
            return $this->get_today_quote($symbol);
        } elseif($date < $now) {
            return $this->get_historic_quote($symbol, $date);
        } elseif($date > $now) {
            throw new QuoteRetrieveException("Cannot get stock quote for future date, unfortunately.");
        }
    }


    /**
     * Retrieves one stock quote for a specific day and stores it in the database with its metadata.
     * @param string $symbol
     * @param string|null $date
     * @return StockQuote
     * @throws QuoteRetrieveException
     * @throws QuoteStoreException
     */
    public function store_one_quote(string $symbol, string $date = null) {

        // Set $date to today's date in YYYY-MM-DD format if not given, otherwise convert $date to YYYY-MM-DD format.
        $date = $date == null ? date("Y-m-d", strtotime("now")) : date("Y-m-d", strtotime($date));

        // Get the HttpSource reference from the environment file.
        $env = env('iex_environment', false); // ['_sandbox', '_production']
        $http_source_ref = env('iex_http_source' . $env, false);

        // Get HttpSource ID by reference.
        $http_source = HttpSource::find_by_reference($http_source_ref);

        // Only get from IEX API if not already exists yet in database, otherwise skip.
        if (!StockQuote::exists($date, $symbol, $http_source->reference)) {
            $quote = $this->get_quote($symbol, $date);

            // Only if a quote was found start the insert process
            if ($quote) {

                // Check if the last_trade_time (in case of today quote) or ... (in case of historic quote) is equal to provided $date. If not, don't save the StockQuote.
                if ($quote->date == $date) {
                    try {
                        $quote->save(); // If all is well, save the quote to the database.
                        return $quote; // Return the quote.
                    } catch(\Exception $e) {
                        throw new QuoteStoreException("StockQuote for symbol " . $symbol . " on date " . $date . " could not be stored because of error: " . $e->getMessage() . ". Full dataset: " . json_encode($quote->toArray(), JSON_PRETTY_PRINT));
                    }
                } else {
                    throw new QuoteStoreException("Skipped. Exchange product with symbol " . $symbol . " wasn't traded at provided date " . $date . ". Latest trade time was " . $quote->date . ".");
                }

            } else {
                throw new QuoteRetrieveException("No quote was retrieved from IEX API for symbol " . $symbol . " and date " . $date . ".");
            }

        } else {
            throw new QuoteStoreException("Skipped. StockQuote for symbol " . $symbol . ", from source " . $http_source->reference . ", on date " . $date ." already exists in database.");
            // Side-note: mind the fact that the US markets open at 09:30 (New York time), which is 15:30 in Amsterdam. That means that until this time, while in NL you might expect the StockQuote for today, IEX would still return the stock quote for yesterday. This might explain the case where you expect today's quote to be freshly added, but you're noticing (above) that it was skipped. Wait until 15:30h.
        }
    }


    /**
     * Function to retrieve and store all quotes of specific type (for instance 'cs' common stock), for one day.
     * @param string $type
     * @param string|null $date
     * @return array
     * @throws QuoteRetrieveException
     */
    public function download_by_type(string $type, string $date = null) {

        // If date is given, convert to YYYY-MM-DD format, otherwise set to null.
        $date = $date ? date('Y-m-d', strtotime($date)) : null;

        // Get all symbols of given type.
        $exchange_product = new ExchangeProduct();
        $exchange_products = $exchange_product->get_all_by_type($type, $date);

        // Set some parameters for the loop.
        $i = 0; // Keep a counter to limit the number of quotes to be stored in case APP_DEBUG is true.
        $max = config('tda.iex_max_quotes_if_appdebug'); // Limit the number of quotes to be stored in case APP_DEBUG is true.
        $max_delay = self::get_delay_seconds(env("APP_DEBUG") ? $max : count($exchange_products)); // Get max delay in seconds for given number of requests.

        // Loop through all symbols and store one quote for each symbol.
        foreach($exchange_products as $symbol) {
            $i++;
            if(env("APP_DEBUG")) {
                if ($i > $max) {
                    break;
                }
            }

            // Dispatch job to queue that in turn calls the store_one_quote method.
            StoreOneQuote::dispatch($symbol->symbol, $date)->delay(now()->addSeconds(rand(0, $max_delay))); // Add random delay for rate limiting.
        }

        // Return a short description of the processes that were triggered.
        return ["count(exchange_products)" => count($exchange_products), "max_delay" => $max_delay];
    }



    /**
     * Give a symbol (for instance: AAPL) and get the quote for today.
     * @param $symbol
     * @return StockQuote
     * @throws QuoteRetrieveException
     */
    public function get_today_quote($symbol) {

        // Retrieve the quote from IEX API.
        $address = $this->environment['api_url'] . "/stable/stock/" . $symbol . "/quote/" . "?token=" . $this->environment['api_token'];
        $response = Http::get($address);

        // Return the StockQuote object.
        if($response->ok()) {

            // Transform response contents into array.
            $response = json_decode($response->body(), true);

            // Convert timestamp to date in YYYY-MM-DD format.
            try {
                $trade_date = self::miliseconds_to_date($response['lastTradeTime']); // Convert timestamp to date in YYYY-MM-DD format.
            } catch (\Exception $e) {
                throw new QuoteRetrieveException("Error converting timestamp to date for " . $symbol . " quote: " . $e->getMessage() . ". Full dataset: " . json_encode($response));
            }

            // Create a new StockQuote object and fill it with the data from the IEX API.
            $stock_quote = new StockQuote();
            $stock_quote->date = $trade_date ?: null;
            $stock_quote->symbol = $response['symbol'];
            $stock_quote->http_source_id = HttpSource::find_by_reference($this->environment['http_source_ref'])->id;
            $stock_quote->average_total_volume = is_numeric($response['avgTotalVolume']) ? floor($response['avgTotalVolume']) : null;
            $stock_quote->volume = is_numeric($response['volume']) ? floor($response['volume']) : null;
            $stock_quote->change = is_numeric($response['change']) ? round($response['change'], 3) : null;
            $stock_quote->change_percentage = is_numeric($response['changePercent']) ? round($response['changePercent'], 2) : null;
            $stock_quote->change_ytd = is_numeric($response['ytdChange']) ? round($response['ytdChange'], 3) : null;
            $stock_quote->open = is_numeric($response['iexOpen']) ? round($response['iexOpen'], 3) : null;
            $stock_quote->close = is_numeric($response['iexClose']) ? round($response['iexClose'], 3) : null;
            $stock_quote->company_name = isset($response['companyName']) ? $response['companyName'] : null;
            $stock_quote->market_cap = is_numeric($response['marketCap']) ? floor($response['marketCap']) : null;
            $stock_quote->pe_ratio = is_numeric($response['peRatio']) ? round($response['peRatio'], 3) : null;
            $stock_quote->week_52_low = is_numeric($response['week52Low']) ? round($response['week52Low'], 3) : null;
            $stock_quote->week_52_high = is_numeric($response['week52High']) ? round($response['week52High'], 3) : null;
            $stock_quote->metadata = $response;

            // Testing / Development
//            $stock_quote->close_time = self::miliseconds_to_date($response['closeTime']) ?: null;
//            $stock_quote->delayed_price_time = self::miliseconds_to_date($response['delayedPriceTime']) ?: null;
//            $stock_quote->extended_price_time = self::miliseconds_to_date($response['extendedPriceTime']) ?: null;
//            $stock_quote->high_time = self::miliseconds_to_date($response['highTime']) ?: null;
//            $stock_quote->iex_close_time = self::miliseconds_to_date($response['iexCloseTime']) ?: null;
//            $stock_quote->iex_last_updated = self::miliseconds_to_date($response['iexLastUpdated']) ?: null;
//            $stock_quote->iex_open_time = self::miliseconds_to_date($response['iexOpenTime']) ?: null;
//            $stock_quote->latest_time = date("Y-m-d", strtotime($response['latestTime'])) ?: null;
//            $stock_quote->latest_update = self::miliseconds_to_date($response['latestUpdate']) ?: null;
//            $stock_quote->low_time = self::miliseconds_to_date($response['lowTime']) ?: null;
//            $stock_quote->open_time = self::miliseconds_to_date($response['openTime']) ?: null;
//            $stock_quote->last_trade_time = self::miliseconds_to_date($response['lastTradeTime']) ?: null;

            return $stock_quote;
        } else {
            throw new QuoteRetrieveException("No quote data was retrieved from IEX API for " . $symbol . " for date " . date("Y-m-d", strtotime("now")) . " (today).");
        }
    }


    /**
     * Give a symbol (for instance: AAPL) and a date and get the quote.
     * @param $symbol
     * @param $date
     * @return StockQuote
     * @throws QuoteRetrieveException
     */
    public function get_historic_quote($symbol, $date) {

        // Retrieve stock quote from IEX API. Notice that date for IEX Cloud API is required in YYYYMMDD format.
        $address = $this->environment['api_url'] . "/stable/stock/" . $symbol . "/chart/date/" . date("Ymd", strtotime($date)) . "?chartByDay=true&token=" . $this->environment['api_token'];
        $response = Http::get($address);

        if($response->ok()) {
            // Transform response contents into array.
            $response = json_decode($response->body(), true);

            // Flatten the output from IEX if necessary
            if (isset($response[0])) {
                $response = $response[0];
            }

            // Force date from response into YYYY-MM-DD format.
            try {
                $trade_date = date("Y-m-d", strtotime($response['date'])); // Convert timestamp to date in YYYY-MM-DD format.
            } catch (\Exception $e) {
                throw new QuoteRetrieveException("Error converting timestamp to date for " . $symbol . " quote: " . $e->getMessage() . ". Full dataset: " . json_encode($response));
            }

            // The if-statement below checks if $response is not empty
            if($response) {

                // Create a new StockQuote object and fill it with the data from the IEX API.
                $stock_quote = new StockQuote();
                $stock_quote->date = $trade_date ?: null;;
                $stock_quote->symbol = $response['symbol'];
                $stock_quote->http_source_id = HttpSource::find_by_reference($this->environment['http_source_ref'])->id;
                $stock_quote->average_total_volume = null;
                $stock_quote->volume = floor($response['volume']) ?: null; // Round down to closest integer.
                $stock_quote->change = ($response['close'] && $response['open']) ? $response['close'] - $response['open'] : null;
                $stock_quote->change_percentage = ($response['close'] && $response['open']) ? ($response['close'] - $response['open']) / $response['open'] : null;
                $stock_quote->change_ytd = null;
                $stock_quote->open = $response['open'] ?: null;
                $stock_quote->close = $response['close'] ?: null;
                $stock_quote->company_name = null;
                $stock_quote->market_cap = null;
                $stock_quote->pe_ratio = null;
                $stock_quote->week_52_low = null;
                $stock_quote->week_52_high = null;
                $stock_quote->metadata = $response;

                // Testing / Development
//                $stock_quote->price_date = date("Y-m-d", strtotime($response['priceDate'])) ?: null;
//                $stock_quote->date_date = date("Y-m-d", strtotime($response['date'])) ?: null;
//                $stock_quote->updated = self::miliseconds_to_date($response['updated']) ?: null;
//                $stock_quote->label = date("Y-m-d", strtotime($response['label'])) ?: null;

                return $stock_quote;
            } else {
                throw new QuoteRetrieveException("No quote data was retrieved from IEX API for " . $symbol . " on " . date("Y-m-d", strtotime($date)) . ". Check whether the given date is in the weekend or on a holiday, or another day where the markets were closed. Furthermore, it could be that the exchange product wasn't traded on this date, or that the product is not traded at all anymore.");
            }

        } else {
            throw new QuoteRetrieveException("No quote data was retrieved from IEX API for " . $symbol . " on " . date("Y-m-d", strtotime($date)) . ". This might be a connection error with IEX API.");
        }
    }


    /**
     * Get all symbols known by IEX, about 12-thousand.
     * @return false|string
     */
    public function get_symbols() {
        $address = $this->environment['api_url'] . "/stable/ref-data/symbols" . "?token=" . $this->environment['api_token'];
        $response = Http::get($address);
        if($response->ok()) {
            return $response->body();
        } else {
            return false;
        }
    }


    /**
     * Retrieve today's set of symbols from IEX API, make an inventory of all NULL FIGIs and duplicate FIGIs, then
     * store the resulting IexHistoricSymbolSetModel in the database (compressed).
     * @return IexHistoricSymbolSetModel|null
     */
    public function download_symbol_set() {
        $symbols = json_decode($this->get_symbols(), true);

        // Compose an array of symbols and metadata to store into the 'symbol_history' table later on.
        $symbol_set_data = [
            'date' => date("Y-m-d", strtotime("now")),
            'metadata' => [
                "datetime" => date("c", strtotime("now")),
                "source" => "iex",
                "count" => count($symbols),
            ],
            'duplicate_figis' => [
                'null' => 0,
            ],
            'symbols' => $symbols,
        ];

        // Loop over the symbols and (1) count Symbols that have null-FIGI, and (2) create an array of all FIGIs.
        $figis = [];
        foreach($symbols as $symbol) {
            if(!$symbol['figi']) {
                $symbol_set_data['duplicate_figis']["null"] += 1; // Count +1 to null-FIGIs
            } else {
                $figis[] = $symbol['figi']; // Add FIGI to full array of FIGIs
            }
        }

        // Count the duplicates in the array of FIGIs.
        $counts = array_count_values($figis);
        foreach($counts as $key => $count) {
            if($count > 1) {
                $symbol_set_data['duplicate_figis'][$key] = $count; // Add to the duplicate-FIGIs array
            }
        }

        // Store resulting SymbolSet in database
        $symbol_set = IexHistoricSymbolSetModel::updateOrCreate(['date' => $symbol_set_data['date']], $symbol_set_data);

        // Return the resulting SymbolSet
        return $symbol_set;
    }


    /**
     * Get maximum number of requests per second for IEX API.
     * @param int $number_of_requests
     * @return int
     * @throws QuoteRetrieveException
     */
    public static function get_delay_seconds(int $number_of_requests) {

        // Set rate levels.
        $rate_level_1 = 100;
        $rate_level_2 = 500;
        $rate_level_3 = 2000;
        $rate_level_4 = 5000;
        $rate_level_5 = 8000;
        $rate_level_6 = 12000;
        $rate_level_7 = 16000;
        $rate_level_8 = 20000;

        // Determine delay seconds based on number of simultaneous requests.
        switch ($number_of_requests) {
            case 0:
                throw new QuoteRetrieveException("There are no requests to be made.");
            case $number_of_requests < $rate_level_1:
                return intval($rate_level_1 / config('tda.iex_max_requests_per_second'));
            case $number_of_requests < $rate_level_2:
                return intval($rate_level_2 / config('tda.iex_max_requests_per_second'));
            case $number_of_requests < $rate_level_3:
                return intval($rate_level_3 / config('tda.iex_max_requests_per_second'));
            case $number_of_requests < $rate_level_4:
                return intval($rate_level_4 / config('tda.iex_max_requests_per_second'));
            case $number_of_requests < $rate_level_5:
                return intval($rate_level_5 / config('tda.iex_max_requests_per_second'));
            case $number_of_requests < $rate_level_6:
                return intval($rate_level_6 / config('tda.iex_max_requests_per_second'));
            case $number_of_requests < $rate_level_7:
                return intval($rate_level_7 / config('tda.iex_max_requests_per_second'));
            case $number_of_requests <= $rate_level_8:
                return intval($rate_level_8 / config('tda.iex_max_requests_per_second'));
            case $number_of_requests > $rate_level_8:
                throw new QuoteRetrieveException("Number of simultaneous requests is too high. You've got to find another way to do this.");
            default: // If no case matches, return 0.
                return 0;
        }
    }


    /**
     * The function below converts a timestamp like "1701377614024" (milliseconds) into a timestamp like "1701377614" (seconds) and then into a date like "2021-01-29".
     * @param $timestamp
     * @return string|void
     */
    public static function miliseconds_to_date($timestamp) {
        if($timestamp) { // Check if $timestamp is not null-ish.
            if(ctype_digit($timestamp)) {
                return date("Y-m-d", substr($timestamp, 0, 10)); // Take first 10 characters of timestamp (seconds since epoch), then compose Y-m-d date.
            }
        }

        return null;
    }


    /**
     * The function below converts a timestamp like "1701377614024" (milliseconds) into a timestamp like "1701377614" (seconds) and then into a date like "2021-01-29".
     * @param $timestamp
     * @return string
     * @throws \Exception
     */
//    public static function get_date($quote)
//    {
//        // Initialize result variable as null.
//        $date = null;
//
//        if(!$date) {
//            if(isset($quote['priceDate'])) { // Check if priceDate is set at all.
//                if($quote['priceDate']) { // Check if priceDate is not null-ish.
//                    $date = date("Y-m-d", strtotime($quote['priceDate']));
//                }
//            }
//        }
//
//        if(!$date) {
//            if(isset($quote['date'])) { // Check if priceDate is set at all.
//                if($quote['date']) { // Check if priceDate is not null-ish.
//                    $date = date("Y-m-d", strtotime($quote['date']));
//                }
//            }
//        }
//
//        if(!$date) {
//            if(isset($quote['label'])) { // Check if label is set at all.
//                if($quote['label']) { // Check if label is not null-ish.
//                    $date = date("Y-m-d", strtotime($quote['label']));
//                }
//            }
//        }
//
//        if(isset($quote['latestUpdate'])) { // Check if latestUpdate is set at all.
//            if($quote['latestUpdate']) { // Check if latestUpdate is not null-ish.
//                if(ctype_digit($timestamp)) {
//                    $date = date("Y-m-d", substr($timestamp, 0, 10)); // Take first 10 characters of timestamp (seconds since epoch), then compose Y-m-d date.
//                }
//            }
//        }
//    }
}
