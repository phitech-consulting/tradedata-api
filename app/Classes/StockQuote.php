<?php

namespace app\Classes;

use App\Classes\HttpSource;
use App\Models\StockQuoteModel;

class StockQuote extends StockQuoteModel
{

    /**
     * Method to determine whether stock quote for particular symbol on one date already exists in DB.
     * @param string $date
     * @param string $symbol
     * @param string $source_ref
     * @return mixed
     */
    public static function exists(string $date, string $symbol, string $source_ref) {

        // Convert date to YYYY-MM-DD format.
        $date = date('Y-m-d', strtotime($date));

        // Get HttpSource by reference
        $http_source = HttpSource::find_by_reference($source_ref);

        // If HttpSource was found, check if StockQuote exists for given date, symbol and HttpSource.
        if($http_source) {
            return StockQuote::where('date', $date)
                ->where('symbol', $symbol)
                ->where('http_source_id', $http_source->id)
                ->exists();
        } else {
            return false; // If no HttpSource was found, return false (not found).
        }
    }
}
