<?php

namespace App\Http\Controllers;

use App\Classes\IexApi;
use Illuminate\Http\Request;

class IexController extends Controller
{

    /**
     * Simply retrieve all symbols directly from IEX and return as JSON.
     * Endpoint: /api/iex/symbols
     * @return string
     */
    public function symbols() {
        $iex = new IexApi();
        $symbols = $iex->get_symbols();
        return response()->json(json_decode($symbols, true));
    }
}
