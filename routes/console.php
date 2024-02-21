<?php

use App\Mail\DebuggingMail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Classes\StockQuote;
use App\Classes\ExchangeProduct;
use Illuminate\Support\Facades\Mail;

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
    $iex_api = new \App\Classes\IexApi();
    $stock_quote = $iex_api->get_quote($symbol, $date);
    $this->line("\n<fg=green>" . print_r($stock_quote->toArray(), true) . "</>");
})->purpose('Get full quote for one single symbol via IEX (give optional date as YYYY-MM-DD)');


/**
 * Calls the get_delay_seconds method (the rate limiter helper) of the IexApi class and prints the result.
 */
Artisan::command('iex:get_delay_seconds {number_of_requests}', function ($number_of_requests) {
    $iex_api = new \App\Classes\IexApi();
    $seconds_delay = $iex_api->get_delay_seconds($number_of_requests);
    $this->line("\n<fg=blue>seconds_delay = " . $seconds_delay . "</>");
})->purpose('Get delay in seconds for given number of requests');


/**
 * Command to store one single quote from IEX API by symbol, optionally on given date.
 */
Artisan::command('iex:store_one_quote {symbol} {date?}', function ($symbol, $date = null) {
    $iex_api = new \App\Classes\IexApi();
    try {
        $stock_quote = $iex_api->store_one_quote($symbol, $date);
        if($stock_quote) {
            $this->line("\n<fg=green>" . print_r($stock_quote->toArray(), true) . "</>");
        } else {
            throw new \Exception("For some reason, the store_one_quote() method returned an empty response.");
        }
    } catch(Exception $e) {
        $this->line("\n<fg=red>" . $e->getTraceAsString() . "\n\n" . $e->getMessage() . "</>\n");
    }
})->purpose('Trigger download process for one single StockQuote (give optional date as YYYY-MM-DD)');


/**
 * Command to trigger download of all quotes of given type (for instance 'cs' common stock), optionally on given date.
 */
Artisan::command('iex:download_by_type {type} {date?}', function ($type, $date = null) {
    $iex_api = new \App\Classes\IexApi();
    try {
        $process_data = $iex_api->download_by_type($type, $date);
        $this->line("\n<fg=green>" . print_r($process_data, true) . "</>");
    } catch(Exception $e) {
        $this->line("\n<fg=red>" . $e->getTraceAsString() . "\n\n" . $e->getMessage() . "</>\n");
    }
})->purpose('Trigger download process for all quotes of given type (give optional date as YYYY-MM-DD)');


/**
 * Commands below are for stock_quote: namespace.
 */


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


/**
 * Command to see if connection to mail server is working.
 */
Artisan::command('tda:testmail {mail}', function ($mail) {
    $data = "If you receive this, the mailserver is working.";
    Mail::to($mail)->send(new DebuggingMail($data));
})->purpose('Check if connection to mailserver is working');


/**
 * Commands below are for report: namespace.
 */


/**
 * Generate Stored Quotes Overview report.
 */
Artisan::command('report:create_sqo {date_from?} {date_to?}', function ($date_from = null, $date_to = null) {

    // Get Collection of StockQuotes between provided date_from and date_to.
    $stock_quotes = \App\Classes\StockQuote::get_by_period(date_from: $date_from, date_to: $date_to);

    // Generate report based on retrieved (lazy) collection.
    $sqo_report = \App\Classes\Report::stored_quotes_overview($stock_quotes);

    // Compose filepath
    $timestamp = \Carbon\Carbon::now()->format('Ymd_His');
    $path = "storage/files/{$timestamp}_sqo.csv";

    // Create and write the file.
    $file = fopen($path, 'w');
    fwrite($file, $sqo_report->csv);
    fclose($file);

    // Feed back to user.
    $this->line("\n<fg=green>Stored Quotes Overview report created at: " . $path . "</>");

})->purpose('Creates a Stored Quotes Overview report and saves it as a CSV file');


/**
 * Generates Weekend Stock Quotes report.
 */
Artisan::command('report:create_wsq {date_from?} {date_to?}', function ($date_from = null, $date_to = null) {

    // Get Collection of StockQuotes between provided date_from and date_to.
    $stock_quotes = \App\Classes\StockQuote::get_by_period(date_from: $date_from, date_to: $date_to);

    // Generate report based on retrieved (lazy) collection.
    $wsq_report = \App\Classes\Report::weekend_stock_quotes($stock_quotes);

    // Compose filepath
    $timestamp = \Carbon\Carbon::now()->format('Ymd_His');
    $path = "storage/files/{$timestamp}_wsq.csv";

    // Create and write the file.
    $file = fopen($path, 'w');
    fwrite($file, $wsq_report->csv);
    fclose($file);

    // Feed back to user.
    $this->line("\n<fg=green>Weekend Stock Quotes report created at: " . $path . "</>");

})->purpose('Creates a Weekend Stock Quotes report and saves it as a CSV file');


/**
 * Commands below are for import: namespace.
 */


Artisan::command('import:get_quote {symbol} {date?}', function ($symbol, $date) {
    $helper = new App\Classes\ImportFromOldVersionHelper;
    $stock_quote = $helper->get_srv1_quote($symbol, $date);
    $this->line("\n<fg=green>" . print_r($stock_quote->toArray(), true) . "</>");
})->purpose('Get full quote for one single symbol via IEX, give required date as YYYY-MM-DD)');

Artisan::command('import:another_day', function () {
    $helper = new App\Classes\ImportFromOldVersionHelper;
    $result = $helper->import_another_day();
    dd($result);
})->purpose('');

Artisan::command('import:one_quote {symbol} {date}', function ($symbol, $date) {
    $helper = new App\Classes\ImportFromOldVersionHelper;
    $result = $helper->import_one_quote($symbol, $date);
    dd($result);
})->purpose('');

Artisan::command('srv1:get_quote_by_id {id}', function ($id) {
    $srv1 = new App\Classes\Srv1();
    $srv1->get_quote_by_measurement_id();
})->purpose('');

Artisan::command('srv1:test', function () {
    dd(\DB::connection('srv1')->getPDO());
    try {
        \DB::connection('srv1')->getPDO();
        echo \DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        echo 'None';
    }
})->purpose('Test the connection to srv1 database');
