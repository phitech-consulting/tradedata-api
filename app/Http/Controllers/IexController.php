<?php

namespace App\Http\Controllers;

use App\Classes\IexApi;
use App\Services\SymbolService;
use Illuminate\Http\Request;

class IexController extends Controller
{
    /**
     * Simply retrieve all symbols directly from IEX and return as JSON.
     * Endpoint: GET /api/iex/symbols
     * @return mixed
     */
    public function symbols() {
        $iex = new IexApi();
        return response()->json($iex->get_symbols());
    }


    /**
     * Return full quote for one single symbol via IEX.
     * Does not do any database operations.
     * Endpoint: GET /api/iex/quote
     * @param $symbol
     * @param $date
     * @return mixed
     * @throws \Exception
     */
    public function get_quote(Request $request) {
        $iex_api = new \App\Classes\IexApi();
        return response($iex_api->get_quote($request->symbol));
    }
}
