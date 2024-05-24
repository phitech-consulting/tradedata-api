<?php

namespace App\Http\Controllers;

use App\Classes\ExchangeProduct;
use App\Models\ExchangeProductModel;
use Illuminate\Http\Request;

class ExchangeProductController extends Controller
{

    /**
     * Display a listing of the resource.
     * Route: GET /api/resources/exchange-products
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $exchange_products = ExchangeProductModel::all();
        if($exchange_products) {
            return $exchange_products->toJson();
        } else {
            return response("No ExchangeProduct records found.", 404);
        }
    }


    /**
     * Store a newly created resource in storage.
     * Route: POST /api/resources/exchange-products
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
     * Route: GET /api/resources/exchange-products/{id}
     * @param  \App\Models\ExchangeProduct $exchangeProductModel
     * @return \Illuminate\Http\Response
     */
    public function show(ExchangeProduct $exchangeProduct)
    {
        if($exchangeProduct) {
            return response()->json($exchangeProduct);
        } else {
            return response("ExchangeProduct not found.", 404);
        }
    }


    /**
     * Update the specified resource in storage.
     * Route: PUT /api/resources/exchange-products/{id}
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
     * Route: DELETE /api/resources/exchange-products/{id}
     * @param  \App\Models\ExchangeProductModel  $exchangeProductModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExchangeProductModel $exchangeProductModel)
    {
        // Not to implement
        return response("", 404);
    }
}
