<?php

namespace App\Classes;

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
            'db_host' => env('iex_db_host' . $env, false),
            'db_name' => env('iex_db_name' . $env, false),
            'db_user' => env('iex_db_user' . $env, false),
            'db_pass' => env('iex_db_pass' . $env, false),
        ];
    }


    /**
     * Give a symbol (for instance: AAPL) and get the quote for today.
     * @param $symbol
     * @return false
     */
    public function get_today_quote($symbol) {
        $address = $this->environment['api_url'] . "/stable/stock/" . $symbol . "/quote/" . "?token=" . $this->environment['api_token'];
        $response = Http::get($address);
        if($response->ok()) {
            return $response->body();
        } else {
            return false;
        }
    }


    /**
     * Give a symbol (for instance: AAPL) and a date and get the quote.
     * @param $symbol
     * @param $date
     * @return false
     */
    public function get_historic_quote($symbol, $date) {
        $address = $this->environment['api_url'] . "/stable/stock/" . $symbol . "/chart/date/" . $date . "?chartByDay=true&token=" . $this->environment['api_token'];
        $response = Http::get($address);
        if($response->ok()) {
            return $response->body();
        } else {
            return false;
        }
    }


    /**
     * Get all symbols known by IEX, about 12-thousand.
     * @return false
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
}
