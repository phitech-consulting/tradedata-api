<?php

use App\Mail\DebuggingMail;
use App\Models\IexHistoricStockQuoteModel;
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
 * Commands below are for exchange_product: namespace.
 */


/**
 * Command to count the total number of ExchangeProducts, optionally on given date and display first two.
 */
Artisan::command('exchange_product:get_all {date?}', function ($date = null) {

    // Retrieve collection of all exchange products of given type and date.
    $quote_service = new ExchangeProduct;
    $exchange_products = $quote_service->get_all($date);

    // The lines below print (1) the first two ExchangeProducts, (2) the number of ExchangeProducts found of type.
    if($exchange_products) {
        $this->line("\n<fg=blue>" . print_r(array_slice($exchange_products->toArray(), 0, 2), true) . "... ...</>");
        $this->line("\n<fg=blue>Total of " . count($exchange_products) . " ExchangeProducts found.</>\n");
    } else {
        $this->line("\n<fg=red>No data was returned.</>\n");
    }

})->purpose('Inspect result of ExchangeProducts->get_all() method (give optional date as YYYY-MM-DD)');


/**
 * Provide a symbol and retrieve its concurring name from the database, from exchange_products table.
 */
Artisan::command('exchange_product:get_name_by_symbol {symbol}', function ($symbol) {

    // Get the name for one symbol and return.
    $ep_service = new ExchangeProduct;
    $exchange_products = $ep_service->get_name_by_symbol($symbol);
    if($exchange_products) {
        $this->line("\n<fg=green>" . $exchange_products . ".</>\n");
    } else {
        $this->line("\n<fg=red>No data was returned.</>");
    }

})->purpose('Get the name for one symbol from exchange_products table');


/**
 * Commands below are for iex: namespace.
 */


/**
 * Retrieve and print all current symbols from IEX API (one of the reference data endpoints).
 */
Artisan::command('iex:get_symbols', function () {
    $iex_api = new \App\Classes\IexApi();
    $symbols = $iex_api->get_symbols();
    $this->line("\n<fg=green>" . print_r($symbols, true) . "</>\n");
})->purpose('Retrieve and print all symbols from IEX API reference data');


/**
 * Retrieve and print quote for one single symbol via IEX.
 */
Artisan::command('iex:get_quote {symbol}', function ($symbol) {
    $iex_api = new \App\Classes\IexApi();
    $stock_quote = $iex_api->get_quote($symbol);
    $this->line("\n<fg=green>" . print_r($stock_quote->toArray(), true) . "</>\n");
})->purpose('Retrieve and print quote for one single symbol via IEX');


/**
 * Command to store one single Top of Book from IEX API by symbol.
 */
Artisan::command('iex:store_one_quote {symbol} {use_last_trading_day?}', function ($symbol, $use_last_trading_day = null) {

    // Convert the integer flag to a boolean value.
    $use_last_trading_day = $use_last_trading_day == 1;

    // Do the operations.
    $iex_api = new \App\Classes\IexApi();
    try {
        $quote = $iex_api->store_one_quote($symbol, $use_last_trading_day);
        if($quote) {
            $this->line("\n<fg=green>" . print_r($quote->toArray(), true) . "</>");
        } else {
            throw new \Exception("For some reason, the store_one_quote() method returned an empty response.");
        }
    } catch(Exception $e) {
        $this->line("\n<fg=red>" . $e->getTraceAsString() . "\n\n" . $e->getMessage() . "</>\n");
    }
})->purpose('Trigger download process for one single quote by given symbol');


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
 * Get and print full quote for one single symbol from database (optionally give date as yyyymmdd).
 */
Artisan::command('stock_quote:get_quote {symbol} {date?}', function ($symbol, $date = null) {
    $stock_quote = new \App\Classes\StockQuote();
    $stock_quote = $stock_quote->get_quote($symbol, $date);
    $this->line("\n<fg=green>" . print_r($stock_quote->toArray(), true) . "</>");
})->purpose('Get full quote from database for one single symbol (give optional date as YYYY-MM-DD)');


/**
 *
 */
Artisan::command('stock_quote:get_min_max_date {symbol}', function ($symbol) {
    $stock_quote = new StockQuote;
    $range = $stock_quote->get_min_max_date($symbol);
    dd($range);
})->purpose('');


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


//Artisan::command('import:one_quote {symbol} {date}', function ($symbol, $date) {
//    $helper = new App\Classes\ImportIexHistoricHelper;
//    $result = $helper->import_one_quote($symbol, $date);
//    dd($result);
//})->purpose('');







Artisan::command('dates_helper:get_dates_sample {symbol}', function ($symbol) {
    $stock_quote = new StockQuote;
    $range = $stock_quote->get_min_max_date($symbol);

//    $sample = \App\Classes\DatesHelper::get_dates_sample("2022-01-01", "2023-12-31", ['spaced_days' => 10]);
//    $sample = \App\Classes\DatesHelper::get_dates_sample("2022-01-01", "2023-12-31", ['sample_size' => 315, "random" => true]);
//    $sample = \App\Classes\DatesHelper::get_dates_sample("2022-01-01", "2023-12-31", ['sample_size' => 24, "random" => true]);
//    $sample = \App\Classes\DatesHelper::get_dates_sample($range->min_date, $range->max_date, ['spaced_days' => 5]);
    $sample = \App\Classes\DatesHelper::get_dates_sample($range->min_date, $range->max_date, ['sample_size' => 10]);
        dd($sample);
//    foreach($sample as $date) {
//        echo $date . "\n";
//    }
})->purpose('');



Artisan::command('dates_helper:get_first_previous_trading_date', function () {
    $date = \App\Classes\DatesHelper::get_first_previous_trading_date();
    dd($date);
})->purpose('');




Artisan::command('quality_check:test_one_reference {date} {symbol}', function ($date, $symbol) {
    \App\Classes\QualityCheck::test_one_reference($date, $symbol);
})->purpose('');
