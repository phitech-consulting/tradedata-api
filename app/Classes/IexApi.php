<?php

namespace App\Classes;

use App\Exceptions\RetrieveQuoteException;
use App\Models\IexHistoricSymbolSetModel;
use Illuminate\Support\Facades\Http;

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
     * Give a symbol (for instance: AAPL) and get the quote for today.
     * @param $symbol
     * @return StockQuote
     * @throws RetrieveQuoteException
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
                $date = self::miliseconds_to_date($response['latestUpdate']); // Convert timestamp to date in YYYY-MM-DD format.
            } catch (\Exception $e) {
                throw new RetrieveQuoteException("Error converting timestamp to date for " . $symbol . " quote: " . $e->getMessage() . ". Full dataset: " . json_encode($response));
            }

            // Create a new StockQuote object and fill it with the data from the IEX API.
            $stock_quote = new StockQuote();
            $stock_quote->date = $date;
            $stock_quote->symbol = $response['symbol'];
            $stock_quote->http_source_id = HttpSource::find_by_reference($this->environment['http_source_ref'])->id;
            $stock_quote->average_total_volume = floor($response['avgTotalVolume']) ?: null;
            $stock_quote->volume = floor($response['volume']) ?: null;
            $stock_quote->change = $response['change'] ?: null;
            $stock_quote->change_percentage = $response['changePercent'] ?: null;
            $stock_quote->change_ytd = $response['ytdChange'] ?: null;
            $stock_quote->open = $response['iexOpen'] ?: null;
            $stock_quote->close = $response['iexClose'] ?: null;
            $stock_quote->company_name = $response['companyName'] ?: null;
            $stock_quote->market_cap = floor($response['marketCap']) ?: null;
            $stock_quote->pe_ratio = $response['peRatio'] ?: null;
            $stock_quote->week_52_low = $response['week52Low'] ?: null;
            $stock_quote->week_52_high = $response['week52High'] ?: null;
            $stock_quote->metadata = $response;
            return $stock_quote;
        } else {
            throw new RetrieveQuoteException("No quote data was retrieved from IEX API for " . $symbol . " for today.");
        }
    }


    /**
     * Give a symbol (for instance: AAPL) and a date and get the quote.
     * @param $symbol
     * @param $date
     * @return StockQuote
     * @throws RetrieveQuoteException
     */
    public function get_historic_quote($symbol, $date) {

        // Retrieve stock quote from IEX API.
        $address = $this->environment['api_url'] . "/stable/stock/" . $symbol . "/chart/date/" . $date . "?chartByDay=true&token=" . $this->environment['api_token'];
        $response = Http::get($address);

        if($response->ok()) {
            // Transform response contents into array.
            $response = json_decode($response->body(), true);

            // Flatten the output from IEX if necessary
            if (isset($response[0])) {
                $response = $response[0];
            }

            // The if-statement below checks if $response is not empty
            if($response) {

                // Create a new StockQuote object and fill it with the data from the IEX API.
                $stock_quote = new StockQuote();
                $stock_quote->date = $response['priceDate'];
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
                return $stock_quote;
            } else {
                throw new RetrieveQuoteException("No quote data was retrieved from IEX API for " . $symbol . " on " . date("Y-m-d", strtotime($date)) . ". Check whether the given date is in the weekend or on a holiday, or another day where the markets were closed. Furthermore, it could be that the exchange product wasn't traded on this date, or that the product is not traded at all anymore.");
            }

        } else {
            throw new RetrieveQuoteException("No quote data was retrieved from IEX API for " . $symbol . " on " . date("Y-m-d", strtotime($date)) . ". This might be a connection error with IEX API.");
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
     * @throws RetrieveQuoteException
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
                throw new RetrieveQuoteException("Number of simultaneous requests is too high. You've got to find another way to do this.");
            default: // If no case matches, return 0.
                return 0;
        }
    }


    /**
     * The function below converts a timestamp like "1701377614024" (milliseconds) into a timestamp like "1701377614" (seconds) and then into a date like "2021-01-29".
     * @param $timestamp
     * @return string
     * @throws \Exception
     */
    public static function miliseconds_to_date($timestamp)
    {
        if (!ctype_digit($timestamp)) {
            throw new \Exception("Provided timestamp is not numeric.");
        }
        $timestamp = substr($timestamp, 0, 10); // Take first 10 characters of timestamp (seconds since epoch).
        return date("Y-m-d", $timestamp);
    }
}
