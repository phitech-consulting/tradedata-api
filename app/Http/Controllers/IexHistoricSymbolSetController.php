<?php

namespace App\Http\Controllers;

use App\Models\IexHistoricSymbolSetModel;
use Illuminate\Http\Request;

class IexHistoricSymbolSetController extends Controller
{

    /**
     * Display a listing of the resource.
     * Route: GET /api/resources/iex-historic-symbol-sets
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $historic_symbol_sets = IexHistoricSymbolSetModel::select('id', 'created_at', 'updated_at', 'date', 'metadata', 'duplicate_figis')->get();
        if($historic_symbol_sets) {
            return $historic_symbol_sets->toJson();
        } else {
            return response("No IexHistoricSymbolSet records found.", 404);
        }
    }


    /**
     * Store a newly created resource in storage.
     * Route: POST /api/resources/iex-historic-symbol-sets
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Not to implement
        return response("", 404);
    }


    /**
     * Display the specified resource.
     * Route: GET /api/resources/iex-historic-symbol-sets/{id}
     * @param  \App\Models\IexHistoricSymbolSetModel  $iexHistoricSymbolSet
     * @return \Illuminate\Http\Response
     */
    public function show(IexHistoricSymbolSetModel $iexHistoricSymbolSet)
    {
        if($iexHistoricSymbolSet) {
            return response()->json($iexHistoricSymbolSet);
        } else {
            return response("IexHistoricSymbolSet not found.", 404);
        }
    }


    /**
     * Update the specified resource in storage.
     * Route: UPDATE /api/resources/iex-historic-symbol-sets/{id}
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\IexHistoricSymbolSetModel  $iexHistoricSymbolSet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IexHistoricSymbolSetModel $iexHistoricSymbolSet)
    {
        // Not to implement
        return response("", 404);
    }


    /**
     * Remove the specified resource from storage.
     * DELETE /api/resources/iex-historic-symbol-sets/{id}
     * @param  \App\Models\IexHistoricSymbolSetModel  $iexHistoricSymbolSet
     * @return \Illuminate\Http\Response
     */
    public function destroy(IexHistoricSymbolSetModel $iexHistoricSymbolSet)
    {
        if($iexHistoricSymbolSet) {
            if($iexHistoricSymbolSet->delete()) {
                return response("IexHistoricSymbolSet deleted.", 200);
            } else {
                return response("IexHistoricSymbolSet not deleted.", 200);
            }
        } else {
            return response("IexHistoricSymbolSet not found.", 404);
        }
    }
}
