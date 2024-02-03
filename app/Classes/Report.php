<?php

namespace app\Classes;

use Illuminate\Database\Eloquent\Collection;

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
                ];
            }

            $report_obj->raw[$date]['mean_fill_rate'][] = $stock_quote->get_fill_rate();
            $report_obj->raw[$date]['count_total']++;

            if(isset($stock_quote->metadata['id']) && $stock_quote->metadata['id'] === "HISTORICAL_PRICES") {
                $report_obj->raw[$date]['count_historic']++;
            } else {
                $report_obj->raw[$date]['count_live']++;
            }
        }

        // Compute the mean fill rates and clean up intermediate storage.
        foreach($report_obj->raw as $date => $data) {
            $report_obj->raw[$date]['mean_fill_rate'] = array_sum($data['mean_fill_rate']) / count($data['mean_fill_rate']);
        }

        // Generate a CSV based on the raw report.
        $report_obj->csv = "date;count_total;count_historic;count_live;mean_fill_rate\n";
        foreach ($report_obj->raw as $date => $data) {
            $report_obj->csv .= $date . ';' . $data['count_total'] . ';' . $data['count_historic'] . ';' . $data['count_live'] . ';' . $data['mean_fill_rate'] . "\n";
        }

        // Return an instance of this class, including raw and csv report.
        return $report_obj;
    }
}
