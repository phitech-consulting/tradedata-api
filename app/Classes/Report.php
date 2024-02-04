<?php

namespace app\Classes;

use Illuminate\Database\Eloquent\Collection;
use App\Classes\DatesHelper;

class Report
{

    public array $raw = [];
    public string $csv = "";

    /**
     * Generate a report of stored StockQuotes based on a provided Collection that includes various metadata and data
     * quality measures.
     * @param Collection $stock_quotes
     * @return self
     */
    public static function stored_quotes_overview($stock_quotes) {

        // Initialize some variables.
        $report_obj = new self();

        // Iterate over the StockQuotes.
        foreach($stock_quotes as $stock_quote)
        {
            $date = $stock_quote->date;

            // Initialize date group if not already set.
            if(!isset($report_obj->raw[$date])) {


                $report_obj->raw[$date] = [
                    'mean_fill_rate' => [],
                    'count_historic' => 0,
                    'count_live' => 0,
                    'count_total' => 0,
                    'count_source_srv1' => 0,
                    'count_source_iex_prd' => 0,
                    'count_source_unknown' => 0,
                    'is_weekend' => DatesHelper::is_weekend($stock_quote->$date) ? "Y" : "N",
                ];
            }

            $report_obj->raw[$date]['mean_fill_rate'][] = $stock_quote->get_fill_rate();
            $report_obj->raw[$date]['count_total']++;

            if(isset($stock_quote->metadata['id']) && $stock_quote->metadata['id'] === "HISTORICAL_PRICES") {
                $report_obj->raw[$date]['count_historic']++;
            } else {
                $report_obj->raw[$date]['count_live']++;
            }

            if($stock_quote->http_source_id == 3) {
                $report_obj->raw[$date]['count_source_srv1']++;
            } elseif($stock_quote->http_source_id == 2) {
                $report_obj->raw[$date]['count_source_iex_prd']++;
            } else {
                $report_obj->raw[$date]['count_source_unknown']++;
            }
        }

        // Compute the mean fill rates and clean up intermediate storage.
        foreach($report_obj->raw as $date => $data) {
            $report_obj->raw[$date]['mean_fill_rate'] = array_sum($data['mean_fill_rate']) / count($data['mean_fill_rate']);
        }

        // Generate a CSV based on the raw report.
        $report_obj->csv = "date;count_total;count_historic;count_live;mean_fill_rate;count_source_srv1;count_source_iex_prd;count_source_unknown;is_weekend\n";
        foreach ($report_obj->raw as $date => $data) {
            $report_obj->csv .= $date . ';' . $data['count_total'] . ';' . $data['count_historic'] . ';' . $data['count_live'] . ';' . $data['mean_fill_rate'] . ';' . $data['count_source_srv1'] . ';' . $data['count_source_iex_prd'] . ';' . $data['count_source_unknown'] . ';' . $data['is_weekend'] . "\n";
        }

        // Return an instance of this class, including raw and csv report.
        return $report_obj;
    }


    // A report that shows all stock-quotes that are for some f*** reason dated in the weekend.
    public static function weekend_stock_quotes($stock_quotes)
    {
        $report_obj = new self();
        $report_obj->raw = [];
        foreach ($stock_quotes as $stock_quote) {
            $date = $stock_quote->date;
            $day_of_week = date('N', strtotime($date));
            if ($day_of_week >= 6) {
                $report_obj->raw[] = $stock_quote;
            }
        }
        return $report_obj;
    }
}
