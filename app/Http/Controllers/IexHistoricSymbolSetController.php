<?php

namespace App\Http\Controllers;

use App\Models\IexHistoricSymbolSet;
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
        // Not to implement
        return response("", 404);
    }

    /**
     * Show the form for creating a new resource.
     * Route: GET /api/resources/iex-historic-symbol-sets/create
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Not to implement
        return response("", 404);
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
     * @param  \App\Models\IexHistoricSymbolSet  $iexHistoricSymbolSet
     * @return \Illuminate\Http\Response
     */
    public function show(IexHistoricSymbolSet $iexHistoricSymbolSet)
    {
        return response()->json($iexHistoricSymbolSet);
    }

    /**
     * Show the form for editing the specified resource.
     * Route: GET /api/resources/iex-historic-symbol-sets/{id}/edit
     * @param  \App\Models\IexHistoricSymbolSet  $iexHistoricSymbolSet
     * @return \Illuminate\Http\Response
     */
    public function edit(IexHistoricSymbolSet $iexHistoricSymbolSet)
    {
        // Not to implement
        return response("", 404);
    }

    /**
     * Update the specified resource in storage.
     * Route: UPDATE /api/resources/iex-historic-symbol-sets/{id}
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\IexHistoricSymbolSet  $iexHistoricSymbolSet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IexHistoricSymbolSet $iexHistoricSymbolSet)
    {
        // Not to implement
        return response("", 404);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/resources/iex-historic-symbol-sets/{id}
     * @param  \App\Models\IexHistoricSymbolSet  $iexHistoricSymbolSet
     * @return \Illuminate\Http\Response
     */
    public function destroy(IexHistoricSymbolSet $iexHistoricSymbolSet)
    {
        // Not to implement
        return response("", 404);
    }
}
