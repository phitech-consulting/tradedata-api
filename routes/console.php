<?php

use App\Classes\IexApi;
use App\Classes\Symbol;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/**
 * Get and print full quote for one single symbol via IEX (optionally give date as
 * YYYYMMDD. Does not do any database operations.
 */
Artisan::command('iex:get_quote {symbol} {date?}', function ($symbol, $date = null) {
    $quote_service = new \App\Services\StockQuoteService();
    print_r(json_decode($quote_service->get_quote($symbol, $date), true));
})->purpose('Get full quote for one single symbol via IEX (give optional date as YYYYMMDD)');


Artisan::command('iex:download_quotes_by_type {type} {date?}', function ($type, $date = null) {
    $quote_service = new \App\Services\StockQuoteService();
    echo $quote_service->download_quotes_by_type($type, $date);
})->purpose('Trigger download process for all quotes of given type (give optional date as YYYYMMDD)');


Artisan::command('stock_quote:exists {date} {type} {source} {symbol}', function ($date, $type, $source, $symbol) {
    $quote_service = new \App\Services\StockQuoteService();
    if($quote_service->exists($date, $type, $source, $symbol)) {
        echo "yes";
    } else {
        echo "no";
    }
})->purpose('Check if a quote for a symbol for one date already exists in DB or not (give date as YYYY-MM-DD)');



Artisan::command('srv1', function () {
    dd(\DB::connection('srv1')->getPDO());
        try {
            \DB::connection('srv1')->getPDO();
            echo \DB::connection()->getDatabaseName();
        } catch (\Exception $e) {
            echo 'None';
        }
})->purpose('Test the connection to srv1 database');


/**
 * Command to show current version of Tradedata API.
 */
Artisan::command('tda:version', function () {
    $this->line("\n<options=bold;fg=magenta>" . App\Classes\TdaSelf::get_version() . "</>\n");
})->purpose('Shows current version of Tradedata API');


/**
 * Command to show metadata about current Tradedata API version.
 */
Artisan::command('tda:metadata', function () {
    echo new App\Classes\TdaSelf;
})->purpose('Shows metadata about current Tradedata API version');


/**
 * Command to show one specific setting by key.
 */
Artisan::command('tda:setting {key}', function ($key) {
    echo "\n" . $key . " = " . config("tda." . $key) . "\n\n";
})->purpose('Show one specific TDA setting by key');

