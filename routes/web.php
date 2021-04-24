<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* Test output pages */
Route::get('output-yahoo-test', [App\Http\Controllers\YahooAPI::class, 'output_yahoo_page_test']);
Route::get('stocks-key-indicators', [App\Http\Controllers\GetYahooData::class, 'output_watchlist_key_indicators']);
Route::get('yahoo-search-symbols', [App\Http\Controllers\GetYahooData::class, 'output_yahoo_symbols']);

