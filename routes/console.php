<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Classes\StockQuote;
use App\Classes\ExchangeProduct;

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
 * Commands below are for exchange_product: namespace.
 */


/**
 * Command to count the number of ExchangeProducts of given type, optionally on given date and display first two.
 */
Artisan::command('exchange_product:get_all_by_type {type} {date?}', function ($type, $date = null) {

    // Retrieve collection of all exchange products of given type and date.
    $quote_service = new ExchangeProduct;
    $exchange_products = $quote_service->get_all_by_type($type, $date);

    // The lines below print (1) the first two ExchangeProducts, (2) the number of ExchangeProducts found of type.
    $this->line("\n<fg=blue>" . print_r(array_slice($exchange_products->toArray(), 0, 2), true) . "... ...</>");
    $this->line("\n<fg=blue>Total of " . count($exchange_products) . " ExchangeProducts found of type " . $type . ".</>\n");

})->purpose('Trigger download process for all quotes of given type (give optional date as YYYY-MM-DD)');


/**
 * Commands below are for iex: namespace.
 */


/**
 * Get and print full quote for one single symbol via IEX (optionally give date as
 * YYYYMMDD. Does not do any database operations.
 */
Artisan::command('iex:get_quote {symbol} {date?}', function ($symbol, $date = null) {
    $quote_service = new StockQuote();
    $stock_quote = $quote_service->get_quote($symbol, $date);
    $this->line("\n<fg=green>" . print_r($stock_quote->toArray(), true) . "</>");
})->purpose('Get full quote for one single symbol via IEX (give optional date as YYYYMMDD)');


/**
 * Calls the get_delay_seconds method (the rate limiter helper) of the IexApi class and prints the result.
 */
Artisan::command('iex:get_delay_seconds {number_of_requests}', function ($number_of_requests) {
    $iex_api = new \App\Classes\IexApi();
    $seconds_delay = $iex_api->get_delay_seconds($number_of_requests);
    $this->line("\n<fg=blue>seconds_delay = " . $seconds_delay . "</>");
})->purpose('Get delay in seconds for given number of requests');


/**
 * Commands below are for stock_quote: namespace.
 */


/**
 * TODO: This command is not yet finished.
 */
Artisan::command('stock_quote:store_one_quote {symbol} {date?}', function ($symbol, $date = null) {
    $stock_quote_service = new StockQuote();
    try {
        $stock_quote = $stock_quote_service->store_one_quote($symbol, $date);
        if($stock_quote) {
            $this->line("\n<fg=green>" . print_r($stock_quote->toArray(), true) . "</>");
        } else {
            $this->line("\n<fg=blue>StockQuote already present in database. Skipped.\n\nSide-note: mind the fact that the US markets open at 09:30 (New York time), which is 15:30 in Amsterdam. That means that until this time, while in NL you might expect the StockQuote for today, IEX would still return the stock quote for yesterday. This might explain the case where you expect today's quote to be freshly added, but you're noticing (above) that it was skipped. Wait until 15:30h.</>\n");
        }
    } catch(Exception $e) {
        $this->line("\n<fg=red>" . $e->getTraceAsString() . "\n\n" . $e->getMessage() . "</>\n");
    }
})->purpose('');


/**
 * Command to trigger download of all quotes of given type (for instance 'cs' common stock), optionally on given date.
 */
Artisan::command('stock_quote:download_by_type {type} {date?}', function ($type, $date = null) {
    $stock_quote = new StockQuote();
    echo $stock_quote->download_by_type($type, $date);
})->purpose('Trigger download process for all quotes of given type (give optional date as YYYYMMDD)');


/**
 * Command to check for existence of a quote for a symbol for one date in DB or not (give date as YYYY-MM-DD).
 */
Artisan::command('stock_quote:exists {date} {symbol} {source_ref}', function ($date, $symbol, $source_ref) {
    $quote_service = new StockQuote();
    if($quote_service->exists($date, $symbol, $source_ref)) {
        $this->line("\n<fg=green>StockQuote with date=" . $date . ", symbol=" . $symbol . ", and source_ref=" . $source_ref . " was found in database.</>\n");
    } else {
        $this->line("\n<fg=red>StockQuote with date=" . $date . ", symbol=" . $symbol . ", and source_ref=" . $source_ref . " was not found in database.</>\n");
    }
})->purpose('Check if a quote for a symbol for one date already exists in DB or not (give date as YYYY-MM-DD)');


/**
 * Commands below are for tda: namespace.
 */


/**
 * Command to test and display the connection to srv1 database.
 */
Artisan::command('tda:srv1', function () {
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

