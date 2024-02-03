<?php

namespace App\Http\Controllers;

use App\Models\ExchangeProductModel;
use Illuminate\Http\Request;

class ExchangeProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // To implement
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Not to implement
        return response("", 404);
    }

    /**
     * Store a newly created resource in storage.
     *
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
     *
     * @param  \App\Models\ExchangeProductModel  $exchangeProductModel
     * @return \Illuminate\Http\Response
     */
    public function show(ExchangeProductModel $exchangeProductModel)
    {
        // To implement
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExchangeProductModel  $exchangeProductModel
     * @return \Illuminate\Http\Response
     */
    public function edit(ExchangeProductModel $exchangeProductModel)
    {
        // Not to implement
        return response("", 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExchangeProductModel  $exchangeProductModel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExchangeProductModel $exchangeProductModel)
    {
        // Not to implement
        return response("", 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExchangeProductModel  $exchangeProductModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExchangeProductModel $exchangeProductModel)
    {
        // Not to implement
        return response("", 404);
    }
}
