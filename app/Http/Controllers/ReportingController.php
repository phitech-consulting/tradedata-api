<?php

namespace App\Http\Controllers;

use App\Classes\StockQuote;
use App\Classes\Report;
use App\Models\ErrorLogModel;
use Illuminate\Http\Request;

class ReportingController extends Controller
{


    public function stored_quotes_overview(Request $request) {

        try {
            // Get Collection of StockQuotes between provided date_from and date_to
            $stock_quotes = StockQuote::get_by_period(date_from: $request->input('date_from'), date_to: $request->input('date_to'));

            // Return in requested (or default) data format.
            $format = $request->input('format') ?? "raw";
            return Report::stored_quotes_overview($stock_quotes)->$format;
        } catch(\Exception $e) {

            // Below a simple script using ErrorLogModel that logs this Exception.
            ErrorLogModel::create([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // TODO: We should also add, later, fill_rate for individual fiels. For instance: fill_rate_symbol, fill_rate_market_cap, et cetera.
    }


    public function weekend_stock_quotes(Request $request) {

        try {
            // Get Collection of StockQuotes between provided date_from and date_to
            $stock_quotes = StockQuote::get_by_period(date_from: $request->input('date_from'), date_to: $request->input('date_to'));

            // Return in requested (or default) data format.
            $format = $request->input('format') ?? "raw";
            return Report::weekend_stock_quotes($stock_quotes)->$format;
        } catch(\Exception $e) {

            // Below a simple script using ErrorLogModel that logs this Exception.
            ErrorLogModel::create([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

    }
}
