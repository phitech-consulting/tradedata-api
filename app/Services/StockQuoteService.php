<?php

namespace App\Services;

use App\Classes\IexApi;
use App\Models\ExchangeProductModel;
use App\Models\IexSymbol;
use App\Models\StockQuoteModel;
use App\Models\StockQuoteMetaModel;
use \Exception;
use \Carbon\Carbon;

class StockQuoteService
{


    /**
     * Get quote for one single symbol.
     * Automatically decide which IEX endpoint to use (today or historic).
     * @param $symbol
     * @param $date
     * @return false|void
     * @throws Exception
     */
    public function get_quote($symbol, $date = null) {
        $now = date("Ymd", strtotime(now()));
        // TODO: Date format validation (must be valid date in format: YYYYMMDD)
        $date = $date ?? $now;
        $iex = new IexApi;

        if($date == $now) {
            return $iex->get_today_quote($symbol);
        } elseif($date < $now) {
            return $iex->get_historic_quote($symbol, $date);
        } elseif($date > $now) {
            throw new Exception("Cannot get stock quote for future date, unfortunately.");
        }
    }


    /**
     * Retrieves one stock quote for a specific day and stores it in the database with its metadata.
     * @param $symbol
     * @param $type
     * @param $date
     * @return void
     * @throws Exception
     */
    public function store_one_quote($symbol, $type, $date) {

        // Only get from IEX API if not already exists yet in database, otherwise skip
        if(!$this->exists(date("Y-m-d", strtotime($date)), $type, 'IEX', $symbol->symbol)) {
            $quote = json_decode($this->get_quote($symbol->symbol, $date), true);
            dd($quote);

            // Only if a quote was found start the insert process
            if($quote) {

                // Flatten the output from IEX if necessary
                if (isset($quote[0])) {
                    $quote = $quote[0];
                }

                // First save main data
                $main = [
                    'date' => date("Y-m-d", strtotime($date)),
                    'type' => $type,
                    'source' => "IEX",
                    'symbol_id' => $symbol->id,
                ];
                $stored_quote = StockQuoteModel::firstOrCreate($main);

                // Only if new record was actually inserted: save metadata
                if ($stored_quote->wasRecentlyCreated) {
                    $meta = [];
                    foreach ($quote as $key => $value) {
                        $meta[] = [
                            'created_at' => Carbon::now()->toDateTimeString(),
                            'updated_at' => Carbon::now()->toDateTimeString(),
                            'stock_quote_id' => $stored_quote->id,
                            'meta_key' => $key,
                            'meta_value' => $value,
                        ];
                    }
                    StockQuoteMetaModel::insert($meta);
                }
            }
            echo "done\n";
        } else {
            echo "skipped\n";
        }
    }


    /**
     * Function to retrieve and store all quotes of specific type (for instance 'cs' common stock), for one day.
     * @param $type
     * @param $date
     * @return void
     * @throws Exception
     */
    public function download_quotes_by_type($type, $date = null) {
        $now = date("Y-m-d", strtotime(now()));
        // TODO: Date format validation (must be valid date in format: YYYYMMDD)
        $date = $date ?? date("Ymd", strtotime(now()));
        $symbol_service = new SymbolService;
        $symbols = $symbol_service->get_all_symbols_by_type($type);
        foreach($symbols as $symbol) {
            $this->store_one_quote($symbol, $type, $date);
        }
    }


    /**
     * Method to determine whether stock quote for particular symbol on one date already exists in DB.
     * @param $date
     * @param $type
     * @param $source
     * @param $symbol
     * @return mixed
     */
    public function exists($date, $type, $source, $symbol) {
        $symbol = ExchangeProductModel::where('symbol', $symbol)->first();
        return StockQuoteModel::where('date', $date)
            ->where('type', $type)
            ->where('source', $source)
            ->where('symbol_id', $symbol->id)
            ->exists();
    }
}
