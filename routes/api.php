<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('trade/prices', [App\Http\Controllers\GetYahooData::class, 'process_post_trade_prices']);
Route::post('trade/search-symbols', [App\Http\Controllers\GetYahooData::class, 'process_post_search_symbols']);
