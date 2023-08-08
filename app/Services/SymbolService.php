<?php

namespace App\Services;

use App\Classes\IexApi;
use App\Models\HistoricSymbolSet;
use Illuminate\Support\Facades\DB;

class SymbolService
{

    /**
     * Enter a type (for instance 'cs' common stock) and get a collection of symbols back from DB.
     * @param string $type
     * @return mixed
     */

    /* TODO: THIS IS WHERE I LEFT OFF. NEXT: (1) IMPLEMENT THAT SYMBOLS ARE RETRIEVED BY DATE, FROM HISTORICSYMBOLSETS (2) ARE DEDUPLICATED, BY USE OF DUPLICATE_FIGIS DELIVERED WITH HISTORIC SYMBOL SET */
    public function get_all_symbols_by_type(string $type, string $date = null) {
        $date = $date ?? date('Y-m-d', strtotime('now'));
        $symbols = DB::table('iex_symbols')->select('symbol', 'id')->where('type', $type)->limit(4)->get();
        return $symbols;
    }


    /**
     * Get all symbols from IEX and upsert to iex_symbols table.
     * @return void
     */
//    public function download_iex_symbols() {
//        $iex = new IexApi;
//        $symbols = json_decode($iex->get_symbols(), true);
//        $values = [];
//        $i = 0;
//
//        // Separate function to do the upsert
//        function upsert_symbols($values) {
//            DB::table('iex_symbols')->upsert(
//                $values,
//                [
//                    ['symbol'],
//                    ['symbol', 'exchange', 'exchange_suffix', 'exchange_name', 'exchange_segment', 'exchange_segment_name', 'name', 'date', 'type', 'iex_id', 'region', 'currency', 'is_enabled', 'figi', 'cik', 'lei']
//                ]
//            );
//        }
//
//        // Prepare array of values
//        foreach($symbols as $symbol) {
//            $values[] = [
//                'symbol' => $symbol['symbol'] ?? "",
//                'exchange' => $symbol['exchange'] ?? "",
//                'exchange_suffix' => $symbol['exchangeSuffix'] ?? "",
//                'exchange_name' => $symbol['exchangeName'] ?? "",
//                'exchange_segment' => $symbol['exchangeSegment'] ?? "",
//                'exchange_segment_name' => $symbol['exchangeSegmentName'] ?? "",
//                'name' => $symbol['name'] ?? "",
//                'date' => $symbol['date'] ?? "",
//                'type' => $symbol['type'] ?? "",
//                'iex_id' => $symbol['iexId'] ?? "",
//                'region' => $symbol['region'] ?? "",
//                'currency' => $symbol['currency'] ?? "",
//                'is_enabled' => $symbol['isEnabled'] ?? "",
//                'figi' => $symbol['figi'] ?? "",
//                'cik' => $symbol['cik'] ?? "",
//                'lei' => $symbol['lei'] ?? ""
//            ];
//            if ($i % 2000 != 0) {
//                upsert_symbols($values);
//                $values = [];
//            }
//            $i++;
//        }
//
//
//        upsert_symbols($values);
//        $values = [];
//    }



    public function download_iex_symbols() {
        $iex = new IexApi();
        $symbols = json_decode($iex->get_symbols(), true);

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
        $symbol_set = HistoricSymbolSet::updateOrCreate(['date' => $symbol_set_data['date']], $symbol_set_data);

        // Return the resulting SymbolSet
        return $symbol_set;
    }
}
//        // Now clear all symbols with duplicated FIGIs from the $symbols variable.
//        foreach($symbols as $key => $symbol) {
//            if(in_array($symbol['figi'], $duplicates)) {
//                unset($symbols[$key]);
//            }
//        }
