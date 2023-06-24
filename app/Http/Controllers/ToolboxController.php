<?php

namespace App\Http\Controllers;

use App\Classes\DatesHelper;
use App\Jobs\IexDownloadDailyCsQuotes;
use App\Services\StockQuoteService;
use Illuminate\Http\Request;

class ToolboxController extends Controller
{
    public function retrieve_quotes_daterange(Request $request) {


        $date_from = $request->date_from;
        $date_to = $request->date_to;


        $dates_range = DatesHelper::createDateRangeArray($date_from, $date_to);

//        foreach($dates_range as $date) {
//            dispatch(new IexDownloadDailyCsQuotes($date));
//        }

        foreach($dates_range as $date) {
            $quote_service = new StockQuoteService;
            $quote_service->download_quotes_by_type('cs', $date);
        }


//        return $dates_range;
    }
}
