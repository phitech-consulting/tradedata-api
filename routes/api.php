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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/**
 * The 'toolbox' endpoints are not part of the interactions between DWH/IEX,
 * but are used for testing and experimenting, for instance via Postman.
 */
Route::prefix('toolbox')->group(function () {
    Route::prefix('quotes')->group(function () {
        Route::post('/retrieve-quotes-daterange', [\App\Http\Controllers\ToolboxController::class, 'retrieve_quotes_daterange']);
    });
});




Route::prefix('iex')->group(function () {
    Route::get('/symbols', [\App\Http\Controllers\IexController::class, 'symbols']);
});




/**
 * Below: The apiResource endpoints make CRUD operations available for other systems.
 * For instance: getting an overview of all symbols, or getting details for one quote.
 */
// All endpoints for Symbols
Route::apiResource('symbols', \App\Http\Controllers\SymbolsController::class);

// All endpoints for Quotes
Route::apiResource('quotes', \App\Http\Controllers\QuotesController::class);
