<?php

namespace App\Services;

use App\Classes\IexApi;
use Illuminate\Support\Facades\DB;

class SymbolService
{

    /**
     * Enter a type (for instance 'cs' common stock) and get a collection of symbols back from DB.
     * @param string $type
     * @return mixed
     */
    public function get_all_symbols_by_type(string $type) {
        $symbols = DB::table('iex_symbols')->select('symbol', 'id')->where('type', $type)->limit(4)->get();
        return $symbols;
    }


    /**
     * Get all symbols from IEX and upsert to iex_symbols table.
     * @return void
     */
    public function download_iex_symbols() {
        $iex = new IexApi;
        $symbols = json_decode($iex->get_symbols(), true);
        $values = [];
        $i = 0;

        // Separate function to do the upsert
        function upsert_symbols($values) {
            DB::table('iex_symbols')->upsert(
                $values,
                [
                    ['symbol'],
                    ['symbol', 'exchange', 'exchange_suffix', 'exchange_name', 'exchange_segment', 'exchange_segment_name', 'name', 'date', 'type', 'iex_id', 'region', 'currency', 'is_enabled', 'figi', 'cik', 'lei']
                ]
            );
        }

        // Prepare array of values
        foreach($symbols as $symbol) {
            $values[] = [
                'symbol' => $symbol['symbol'] ?? "",
                'exchange' => $symbol['exchange'] ?? "",
                'exchange_suffix' => $symbol['exchangeSuffix'] ?? "",
                'exchange_name' => $symbol['exchangeName'] ?? "",
                'exchange_segment' => $symbol['exchangeSegment'] ?? "",
                'exchange_segment_name' => $symbol['exchangeSegmentName'] ?? "",
                'name' => $symbol['name'] ?? "",
                'date' => $symbol['date'] ?? "",
                'type' => $symbol['type'] ?? "",
                'iex_id' => $symbol['iexId'] ?? "",
                'region' => $symbol['region'] ?? "",
                'currency' => $symbol['currency'] ?? "",
                'is_enabled' => $symbol['isEnabled'] ?? "",
                'figi' => $symbol['figi'] ?? "",
                'cik' => $symbol['cik'] ?? "",
                'lei' => $symbol['lei'] ?? ""
            ];
            if ($i % 2000 != 0) {
                upsert_symbols($values);
                $values = [];
            }
            $i++;
        }


        upsert_symbols($values);
        $values = [];
    }
}
