<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;

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
 * A set of API-routes for reading, editing, adding and deleting settings.
 */
Route::prefix('settings')->group(function () {
    Route::get('/about', [SettingsController::class, 'about']);
    Route::post('/', [SettingsController::class, 'addSetting']);
    Route::put('/{id}', [SettingsController::class, 'editSetting']);
    Route::delete('/{id}', [SettingsController::class, 'deleteSetting']);
    Route::get('/{key}', [SettingsController::class, 'getSetting']);
    Route::get('/', [SettingsController::class, 'getAllSettings']);
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
    Route::get('/quote', [\App\Http\Controllers\IexController::class, 'get_quote']);
});


// All endpoints for Symbols
Route::prefix('symbols')->group(function () {
    Route::get('', [\App\Http\Controllers\SymbolsController::class, 'index']);
    Route::get('{symbol}', [\App\Http\Controllers\SymbolsController::class, 'show']);
});

/**
 * Below: The apiResource endpoints make CRUD operations available for other systems.
 * For instance: getting an overview of all symbols, or getting details for one quote.
 */

// All endpoints for Quotes
Route::apiResource('quotes', \App\Http\Controllers\QuotesController::class);


/**
 *
 */
Route::resource('resources/iex-historic-symbol-sets', \App\Http\Controllers\IexHistoricSymbolSetController::class);
