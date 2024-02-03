<?php

namespace app\Classes;

use Illuminate\Database\Eloquent\Collection;

class Report
{

    public array $raw;
    public string $csv;

    /**
     * Generate a report of stored StockQuotes based on a provided Collection that includes various metadata and data
     * quality measures.
     * @param Collection $stock_quotes
     * @return self
     */
    public static function stored_quotes_overview(Collection $stock_quotes) {

        // Initialize some variables.
        $fill_rates_per_date = [];
        $report_obj = new self();

        // Get fill rate for each individual StockQuote.
        foreach($stock_quotes as $stock_quote) {
            $fill_rates_per_date[$stock_quote->date][] = $stock_quote->get_fill_rate(); // Compose array with all fill_rates to get the mean per date later on
        }

        // Get means of fill rate per date.
        foreach($fill_rates_per_date as $date => $fill_rates) {
            $report_obj->raw[$date]['mean_fill_rate'] = array_sum($fill_rates) / count($fill_rates);
        }

        // Get total count of StockQuotes, grouped per date.
        foreach($stock_quotes as $stock_quote) {
            if(!isset($report_obj->raw[$stock_quote->date]['count_total'])) {
                $report_obj->raw[$stock_quote->date]['count_total'] = 0;
            }
            $report_obj->raw[$stock_quote->date]['count_total']++;
        }

        // Get total count of StockQuotes, grouped per date.
        foreach($stock_quotes as $stock_quote) {
            if(!isset($report_obj->raw[$stock_quote->date]['count_historic'])) {
                $report_obj->raw[$stock_quote->date]['count_historic'] = 0;
                $report_obj->raw[$stock_quote->date]['count_live'] = 0;
            }
            if(isset($stock_quote->metadata['id']) && $stock_quote->metadata['id'] == "HISTORICAL_PRICES") {
                $report_obj->raw[$stock_quote->date]['count_historic']++;
            } else {
                $report_obj->raw[$stock_quote->date]['count_live']++;
            }
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
