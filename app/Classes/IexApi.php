<?php

namespace App\Classes;

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
}
