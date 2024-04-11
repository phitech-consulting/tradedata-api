<?php

namespace App\Http\Controllers;

use App\Classes\DatesHelper;
use App\Jobs\IexDownloadDailyCsQuotes;
use App\Services\StockQuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ToolboxController extends Controller
{
    public function get_daterange(Request $request) {
        $date_from = $request->date_from;
        $date_to = $request->date_to;
        $dates_range = DatesHelper::createDateRangeArray($date_from, $date_to);
        return $dates_range;
    }
}
