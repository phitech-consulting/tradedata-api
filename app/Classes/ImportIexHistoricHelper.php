<?php

namespace App\Classes;

use App\Exceptions\QuoteRetrieveException;
use App\Exceptions\QuoteStoreException;
use App\Jobs\ImportOneQuote;
use App\Models\IexHistoricStockQuoteModel;
use App\Models\ImportPlanModel;
use Illuminate\Support\Facades\DB;

class ImportIexHistoricHelper
{



    public static function import_another_day() {

        $import_plan = ImportPlanModel::where('done', 0)->orderBy('date', 'desc')->first();

        $ep_helper = new ExchangeProduct;
        $exchange_products = $ep_helper->get_all_by_type('cs', $import_plan->symbol_set);

        // Set some parameters for the loop.
        $i = 0; // Keep a counter to limit the number of quotes to be stored in case APP_DEBUG is true.
        $max = config('tda.iex_max_quotes_if_appdebug'); // Limit the number of quotes to be stored in case APP_DEBUG is true.
        $max_delay = IexApi::get_delay_seconds(env("APP_DEBUG") ? $max : count($exchange_products)); // Get max delay in seconds for given number of requests.

        // Loop through all symbols and store one quote for each symbol.
        foreach($exchange_products as $symbol) {
            $i++;
            if(env("APP_DEBUG")) {
                if ($i > $max) {
                    break;
                }
            }

            // Dispatch job to queue that in turn calls the store_one_quote method.
            ImportOneQuote::dispatch($symbol->symbol, $import_plan->date)->delay(now()->addSeconds(rand(0, $max_delay))); // Add random delay for rate limiting.
        }

        // Set ImportPlan to done.
        $import_plan->update(['done' => 1]);

        // Return a short description of the processes that were triggered.
        return ["count(exchange_products)" => count($exchange_products), "max" => $max, "max_delay" => $max_delay];
    }


    public function import_one_quote($symbol, $date) {

        // Convert provided date to YYYY-MM-DD format, otherwise set to null.
        $date = date('Y-m-d', strtotime($date));

        // If requested IexHistoricStockQuote isn't registered yet.
        if(!IexHistoricStockQuoteModel::exists($date, $symbol)) {
            $iex_api = new IexApi();
            $quote = $iex_api->get_historic_quote($symbol, $date);

            // Only if a quote was found, start the insert process
            if($quote) {

                // Check if the last_trade_time (in case of today quote) or ... (in case of historic quote) is equal to provided $date. If not, don't save the StockQuote.
                if($quote->date == $date) {
                    try {
                        $iex_historic_stock_quote = new IexHistoricStockQuoteModel();
                        $iex_historic_stock_quote->date = $date;
                        $iex_historic_stock_quote->symbol = $symbol;
                        $iex_historic_stock_quote->quote_data = $quote->metadata;
                        $iex_historic_stock_quote->save(); // If all is well, save the historic quote to the database.
                        return $quote; // Return the historic quote.
                    } catch(\Exception $e) {
                        throw new QuoteStoreException("StockQuote for symbol " . $symbol . " on date " . $date . " could not be stored because of error: " . $e->getMessage() . ". Full dataset: " . json_encode($quote->toArray(), JSON_PRETTY_PRINT));
                    }
                } else {
                    throw new QuoteStoreException("Skipped. Exchange product with symbol " . $symbol . " wasn't traded at provided date " . $date . ". Latest trade time was " . $quote->date . ".");
                }

            } else {
                throw new QuoteRetrieveException("No quote was retrieved from IEX API for symbol " . $symbol . " and date " . $date . ".");
            }

        } else {
            throw new QuoteStoreException("Skipped. StockQuote for symbol " . $symbol . " on date " . $date ." already exists in database.");
            // Side-note: mind the fact that the US markets open at 09:30 (New York time), which is 15:30 in Amsterdam. That means that until this time, while in NL you might expect the StockQuote for today, IEX would still return the stock quote for yesterday. This might explain the case where you expect today's quote to be freshly added, but you're noticing (above) that it was skipped. Wait until 15:30h.
        }
    }
}
