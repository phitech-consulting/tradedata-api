<?php

namespace App\Http\Controllers;

use App\Models\HistoricSymbolSet;
use Illuminate\Http\Request;

class HistoricSymbolSetController extends Controller
{
    /**
     * Get set of SymbolSets.
     * Endpoint: GET /api/symbol-sets
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request) {
        return "Not implemented.";
    }


    /**
     * Create one SymbolSet.
     * Endpoint: POST /api/symbol-sets
     * @param Request $request
     * @return void
     */
    public function store(Request $request) {
        return "Not implemented.";
    }


    /**
     * Get one SymbolSet.
     * Endpoint: GET /api/symbol-sets/{id}
     * @param $date
     * @return mixed
     */
    public function show($date) {
        $symbolSet = HistoricSymbolSet::where('date', $date)->first();
        dd($symbolSet->symbols);
        return "To implement.";
    }


    /**
     * Update one SymbolSet.
     * Endpoint: PUT /api/symbol-sets/{id}
     * @param Request $request
     * @param $date
     * @return void
     */
    public function update(Request $request, $date) {
        return "Not implemented.";
    }


    /**
     * Delete one SymbolSet.
     * Endpoint: DELETE /api/symbol-sets/{date}
     * @param $date
     * @return void
     */
    public function destroy($date) {
        return "Not implemented.";
    }
}
