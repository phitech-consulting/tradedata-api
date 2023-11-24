<?php

namespace App\Http\Controllers;

use App\Http\Requests\SymbolsRequest;
use App\Models\HistoricSymbolSet;
use Illuminate\Http\Request;

class SymbolsController extends Controller
{
    /**
     * Get set of Symbols.
     * Endpoint: GET /api/symbols
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request) {

        $symbolSet = HistoricSymbolSet::where('date', $date)->first();
        dd($symbolSet->symbols);
        return "To implement.";
    }


    /**
     * Get one Symbol.
     * Endpoint: GET /api/symbols/{}
     * @param $id
     * @return mixed
     */
    public function show(SymbolsRequest $request) {

        $validated = $request->validated();
        dd($request->date);
    }

}
