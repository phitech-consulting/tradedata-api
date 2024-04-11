<?php

namespace App\Classes;

use App\Models\HistoricReferenceModel;
use App\Models\IexHistoricStockQuoteModel;
use \Exception;

class QualityCheck
{



    public static function test_one_reference($date, $symbol) {


        if(!HistoricReferenceModel::where("date", $date)->where("symbol", $symbol)->exists()) {
            $stored_quote = StockQuote::where("date", $date)->where("symbol", $symbol)->first();
            $iex_api = new IexApi;
            $historic_quote = $iex_api->get_historic_quote($symbol, $date);

            // Put the Historic Stock Quote in a separate table for reference later on.
            if(!IexHistoricStockQuoteModel::where('date', $date)->where('symbol', $symbol)->first()) {
                IexHistoricStockQuoteModel::create([
                    'date' => $historic_quote->date,
                    'symbol' => $historic_quote->symbol,
                    'quote_data' => $historic_quote->metadata,
                ]);
            }

            // Only if there are both Historic Quote and Stored Quote.
            if($historic_quote && $stored_quote) {
                $quality_check = new self;
                $historic_reference = $quality_check->store_one_historic_reference($historic_quote, $stored_quote);
            } else {
                throw new Exception("Missing historic quote or stored quote.");
            }
            dd($historic_reference);
        } else {
            dd("Historic Reference already exists.");
        }



    }


    public function store_one_historic_reference(StockQuote $historic_quote, StockQuote $stored_quote) {

        $diff_perc_close = null;
        if($historic_quote->close && $stored_quote->close) {
            $diff_perc_close = StatisticsHelper::softmax([$historic_quote->close, $stored_quote->close]);
        }

        $diff_perc_open = null;
        if($historic_quote->open && $stored_quote->open) {
            $diff_perc_open = StatisticsHelper::softmax([$historic_quote->open, $stored_quote->open]);
        }

        $diff_perc_change = null;
        if($historic_quote->change && $stored_quote->change) {
            $diff_perc_change = StatisticsHelper::softmax([$historic_quote->change, $stored_quote->change]);
        }

        $diff_change_perc = null;
        if($historic_quote->change_percentage && $stored_quote->change_percentage) {
            if ($historic_quote->change_percentage > $stored_quote->change_percentage) {
                $diff_change_perc = $historic_quote->change_percentage - $stored_quote->change_percentage;
            } else {
                $diff_change_perc = $stored_quote->change_percentage - $historic_quote->change_percentage;
            }
        }

        return HistoricReferenceModel::create([
            "symbol" => $historic_quote->symbol,
            "date" => $historic_quote->date,
            "diff_perc_close" => $diff_perc_close[0],
            "diff_perc_open" => $diff_perc_open[0],
            "diff_perc_change" => $diff_perc_change[0],
            "diff_change_perc" => $diff_change_perc,
        ]);
    }
}
