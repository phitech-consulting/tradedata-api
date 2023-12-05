<?php

namespace app\Classes;

use App\Classes\ExchangeProduct;
use App\Classes\HttpSource;
use App\Exceptions\RetrieveQuoteException;
use App\Jobs\StoreOneQuote;
use App\Models\StockQuoteMetaModel;
use App\Models\StockQuoteModel;
use App\Classes\IexApi;
use Carbon\Carbon;

class StockQuote extends StockQuoteModel
{

    /**
     * Get quote for one single symbol. Automatically decide which IEX endpoint to use (today or historic).
     * @param $symbol
     * @param $date
     * @return false|void
     * @throws RetrieveQuoteException
     */
    public function get_quote($symbol, $date = null) {

        // If date is given, convert to YYYYMMDD format, otherwise set to null.
        $date = $date ? date('Ymd', strtotime($date)) : null;

        // Set today's date in YYYYMMDD format.
        $now = date("Ymd", strtotime(now()));

        // If date is not given, set to today's date.
        $date = $date ?? $now;

        // Initialize IexApi class.
        $iex = new IexApi();

        // Return quote based on date.
        if($date == $now) {
            return $iex->get_today_quote($symbol);
        } elseif($date < $now) {
            return $iex->get_historic_quote($symbol, $date);
        } elseif($date > $now) {
            throw new RetrieveQuoteException("Cannot get stock quote for future date, unfortunately.");
        }
    }


    /**
     * Retrieves one stock quote for a specific day and stores it in the database with its metadata.
     * @param string $symbol
     * @param string $date
     * @return StockQuote|false
     * @throws RetrieveQuoteException
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
        if(!$this->exists($date, $symbol, $http_source->reference)) {
            $quote = $this->get_quote($symbol, $date);

            // Only if a quote was found start the insert process
            if($quote) {

                // Check again if quote not already exists in database, but this time by use of the retrieved StockQuote date.
                if(!$this->exists($quote->date, $symbol, $http_source->reference)) {
                    $quote->save(); // If all is well, save the quote to the database.
                    return $quote; // Return the quote.
                }

                // Here 'false' should be interpreted as "skipped" by the calling function. StockQuote already exists in database.
                return false;

            } else {
                throw new RetrieveQuoteException("No quote was retrieved from IEX API for symbol " . $symbol . " and date " . $date . ".");
            }

        } else {

            // Here 'false' should be interpreted as "skipped" by the calling function. StockQuote already exists in database.
            return false;
        }
    }


    /**
     * Function to retrieve and store all quotes of specific type (for instance 'cs' common stock), for one day.
     * @param string $type
     * @param string|null $date
     * @return void
     * @throws RetrieveQuoteException
     */
    public function download_by_type(string $type, string $date = null) {

        // If date is given, convert to YYYY-MM-DD format, otherwise set to null.
        $date = $date ? date('Y-m-d', strtotime($date)) : null;

        // Get all symbols of given type.
        $exchange_product = new ExchangeProduct();
        $exchange_products = $exchange_product->get_all_by_type($type, $date);

        // Set some parameters for the loop.
        $i = 0; // Keep a counter to limit the number of quotes to be stored in case APP_DEBUG is true.
        $max = 5; // Limit the number of quotes to be stored in case APP_DEBUG is true.
        $max_delay = IexApi::get_delay_seconds(env("APP_DEBUG") ? $max : count($exchange_products)); // Get max delay in seconds for given number of requests.

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
    }


    /**
     * Method to determine whether stock quote for particular symbol on one date already exists in DB.
     * @param string $date
     * @param string $symbol
     * @param string $source_ref
     * @return mixed
     */
    public function exists(string $date, string $symbol, string $source_ref) {

        // Convert date to YYYY-MM-DD format.
        $date = date('Y-m-d', strtotime($date));

        // Get HttpSource by reference
        $http_source = HttpSource::find_by_reference($source_ref);

        // If HttpSource was found, check if StockQuote exists for given date, symbol and HttpSource.
        if($http_source) {
            return StockQuoteModel::where('date', $date)
                ->where('symbol', $symbol)
                ->where('http_source_id', $http_source->id)
                ->exists();
        } else {
            return false; // If no HttpSource was found, return false (not found).
        }
    }
}
