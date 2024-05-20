<?php

namespace App\Classes;

use App\Exceptions\QuoteRetrieveException;
use App\Exceptions\QuoteStoreException;
use App\Jobs\StoreOneQuote;
use App\Models\IexHistoricSymbolSetModel;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class IexApi
{
    public $environment = [];


    public function __construct() {
        $this->environment = [
            'api_key' => env('IEX_API_KEY', false),
            'api_secret' => env('IEX_API_SECRET', false),
            'base_url' => env('IEX_BASE_URL', false),
        ];
    }


    /**
     * Get all symbols known by IEX, about 11-thousand.
     * @return false|string
     */
    public function get_symbols() {
        $address = $this->environment['base_url'] . "/v1/data/CORE/REF_DATA_IEX_SYMBOLS?token=" . $this->environment['api_secret'];
        $response = Http::get($address);
        if($response->ok()) {
            return json_decode($response->body(), true);
        } else {
            return false;
        }
    }


    /**
     * Retrieve today's set of symbols from IEX API, then compress and store the resulting IexHistoricSymbolSetModel in
     * the database.
     * @return IexHistoricSymbolSetModel|null
     */
    public function download_symbol_set() {
        $symbols = $this->get_symbols();

        // Compose an array of symbols and metadata to store into the 'iex_historic_symbol_sets' table later on.
        $symbol_set_data = [
            'date' => $symbols[0]['date'], // Use the date denoted with the first symbol.
            'metadata' => [
                "datetime" => date("c", strtotime("now")),
                "source" => "iex",
                "count" => count($symbols),
            ],
            'symbols' => $symbols,
        ];

        // Store resulting SymbolSet in database
        $symbol_set = IexHistoricSymbolSetModel::updateOrCreate(['date' => $symbol_set_data['date']], $symbol_set_data);

        // Return the resulting SymbolSet
        return $symbol_set;
    }


    /**
     * Give a symbol (for instance: AAPL) and get the quote for today.
     * @param $symbol
     * @return StockQuote
     * @throws QuoteRetrieveException
     */
    public function get_quote($symbol) {

        // Retrieve the quote from IEX API.
        $address = $this->environment['base_url'] . "/v1/data/core/iex_tops/" . $symbol . "?token=" . $this->environment['api_secret'];
        $response = Http::get($address);

        // Return the StockQuote object.
        if($response->ok()) {

            // Transform response contents into array and flatten.
            $response = json_decode($response->body(), true);

            // Another check if there is actually a response.
            if(isset($response[0])) {

                // Flatten the response.
                $response = $response[0];

                // Convert timestamp to date in YYYY-MM-DD format.
                try {
                    $trade_date = DatesHelper::miliseconds_to_date($response['lastSaleTime']); // Convert timestamp to date in YYYY-MM-DD format.
                    $last_update = DatesHelper::miliseconds_to_date($response['lastUpdated']); // Convert timestamp to date in YYYY-MM-DD format.
                } catch (\Exception $e) {
                    throw new QuoteRetrieveException("Error converting timestamp to date for " . $symbol . " quote: " . $e->getMessage() . ". Full dataset: " . json_encode($response));
                }

                if($trade_date) {
                    // Create a new StockQuote object and fill it with the data from the IEX API.
                    $stock_quote = new StockQuote();
                    $stock_quote->date = $trade_date ?: null;
                    $stock_quote->symbol = $response['symbol'];
                    $stock_quote->http_source_id = HttpSource::find_by_reference('iex')->id;
                    $stock_quote->volume = is_numeric($response['volume']) ? floor($response['volume']) : null;
                    $stock_quote->close = is_numeric($response['lastSalePrice']) ? round($response['lastSalePrice'], 3) : null;
                    $stock_quote->company_name = ExchangeProduct::get_name_by_symbol($symbol) ?? null;
                    $stock_quote->metadata = $response;
                    $stock_quote->type = $response['securityType'] ?? "";
                    $stock_quote->sector = $response['sector'] ?? "";
                    return $stock_quote;
                } else {
                    $close = is_numeric($response['lastSalePrice']) ? round($response['lastSalePrice'], 3) : null;
                    throw new QuoteRetrieveException("It seems symbol " . $symbol . " was not traded on given date given that lastSaleTime is null. Last update time is: " . $last_update . ". Close price is: " . $close . ".");
                }

            } else {
                throw new QuoteRetrieveException("There was a successful request to IEX API, but no data was returned for " . $symbol . " for date " . date("Y-m-d", strtotime("now")) . " (today). One reason for this might be a non-existent symbol.");
            }

        } else {
            throw new QuoteRetrieveException("No quote data was retrieved from IEX API for " . $symbol . " for date " . date("Y-m-d", strtotime("now")) . " (today).");
        }
    }


    /**
     * Retrieves one stock quote for a specific day and stores it in the database with its metadata.
     * @param string $symbol
     * @return StockQuote
     * @throws QuoteRetrieveException
     * @throws QuoteStoreException
     */
    public function store_one_quote(string $symbol, $use_last_trading_day = false) {

        // Set $date to today's date in yyyy-mm-dd format.
        $date = date("Y-m-d", strtotime("now"));

        // If $use_last_trading_day enabled, don't use today's date, but first previous trading day.
        if($use_last_trading_day) {
            $date = DatesHelper::get_first_previous_trading_date();
        }

        // Get HttpSource ID by reference.
        $http_source = HttpSource::find_by_reference('iex');

        // Only get from IEX API if not already exists yet in database, otherwise skip.
        if (!StockQuote::exists($date, $symbol, $http_source->reference)) {
            $quote = $this->get_quote($symbol);

            // Only if a quote was found, start the insert process
            if($quote) {

                // Check if the lastSaleTime is equal to provided $date. If not, don't save the StockQuote.
                if($quote->date == $date) {
                    try {
                        $quote->save(); // If all is well, save the quote to the database.
                        return $quote; // Return the quote.
                    } catch(\Exception $e) {
                        throw new QuoteStoreException("StockQuote for symbol " . $symbol . " on date " . $date . " could not be stored because of error: " . $e->getMessage() . ". Full dataset: " . json_encode($quote->toArray(), JSON_PRETTY_PRINT));
                    }
                } else {
                    $lastUpdate = DatesHelper::miliseconds_to_date($quote->metadata['lastUpdated']);
//                    throw new QuoteStoreException("Skipped. Exchange product with symbol " . $symbol . " wasn't traded at provided date " . $date . ". Latest update time was " . $lastUpdate . ".");
                    throw new QuoteStoreException("Skipped. Quote date (" . $quote->date . ") for " . $symbol . " is not same as provided date " . $date . ". Latest update time was " . $lastUpdate . ".");
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
     * Function to retrieve and store all quotes for today.
     * @return array
     * @throws QuoteRetrieveException
     */
    public function download_all_quotes($use_last_trading_day = false) {

        // Get all symbols of given type.
        $exchange_product = new ExchangeProduct();
        $exchange_products = $exchange_product->get_all();

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
            StoreOneQuote::dispatch($symbol->symbol, $use_last_trading_day)->delay(now()->addSeconds(rand(0, $max_delay))); // Add random delay for rate limiting.
        }

        // Return a short description of the processes that were triggered.
        return ["count(exchange_products)" => count($exchange_products), "max" => $max, "max_delay" => $max_delay];
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
}
