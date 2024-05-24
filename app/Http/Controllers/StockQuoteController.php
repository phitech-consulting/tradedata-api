<?php

namespace App\Http\Controllers;

use App\Models\StockQuoteModel;
use Illuminate\Http\Request;

class StockQuoteController extends Controller
{

    /**
     * Display a listing of the resource.
     * Route: GET /api/resources/stock-quotes
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     * Route: POST /api/resources/stock-quotes
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Display the specified resource.
     * Route: GET /api/resources/stock-quotes/{id}
     * @param  \App\Models\StockQuoteModel  $stockQuoteModel
     * @return \Illuminate\Http\Response
     */
    public function show(StockQuoteModel $stockQuoteModel)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     * Route: PUT /api/resources/stock-quotes/{id}
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockQuoteModel  $stockQuoteModel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StockQuoteModel $stockQuoteModel)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     * Route: DELETE /api/resources/stock-quotes/{id}
     * @param  \App\Models\StockQuoteModel  $stockQuoteModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockQuoteModel $stockQuoteModel)
    {
        //
    }
}
