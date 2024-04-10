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

    /**
     * Method to calculate the fill rate for the current StockQuote object. It calculates the amount of filled
     * fillable fields and divides it by the total count of 'fillable' fields. Returns the mean number of filled
     * fields. The fill rate could be used to determine the object completeness in data.
     * @return float|int
     */
    public function get_fill_rate() {

        // Initialize empty $filled variable.
        $filled = 0;

        // Get the fillable fields for StockQuoteModel.
        $fields = $this->fillable;

        // For each of the fillable fields, check if it is actually filled.
        foreach($fields as $field) {
            if($this->$field != null) {
                $filled++;
            }
        }

        // Return the mean number of filled fields.
        return $filled / count($fields);
    }


    /**
     * Method to get the stock quotes by period, based on the specified start and end dates. Dates date_from and
     * date_to are provided in YYYY-MM-DD format.
     * @param string|null $date_from
     * @param string|null $date_to
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function get_by_period(string $date_from = null, string $date_to = null) {

        // Initialize query.
        $query = StockQuote::query();

        // Optionally add from clause to query.
        if ($date_from) {
            $query->where('date', '>=', $date_from);
        }

        // Optionally add to clause to query.
        if ($date_to) {
            $query->where('date', '<=', $date_to);
        }

        // Order the query.
        $query->orderBy('date');

        // Return the LazyCollection instance.
        return $query->cursor();
    }


    public function get_min_max_date($symbol) {
        return StockQuote::selectRaw('MIN(date) as min_date, MAX(date) as max_date')->where("symbol", $symbol)->first();
    }

}
