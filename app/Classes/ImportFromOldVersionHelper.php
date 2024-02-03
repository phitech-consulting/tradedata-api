<?php

namespace App\Classes;

use App\Exceptions\QuoteRetrieveException;
use App\Exceptions\QuoteStoreException;
use App\Jobs\ImportOneQuote;
use App\Models\ImportPlanModel;
use Illuminate\Support\Facades\DB;

class ImportFromOldVersionHelper
{

    /**
     * Get quote from specific date, by symbol, from SRV1.
     * @param $symbol
     * @param $date
     * @return StockQuote
     * @throws QuoteRetrieveException
     */
    public function get_srv1_quote($symbol, $date) {

        // If date is given, convert to YYYY-MM-DD format, otherwise set to null.
        $date = date('Y-m-d', strtotime($date));

        // Get quotes and return as array.
        $quotes = DB::connection('srv1')->table('dwh_market_data.measurements')
            ->whereDate('measurement_datetime', $date)
            ->where('symbol', $symbol)
            ->take(1)
            ->get(['measurement_id', 'measurement_datetime', 'symbol'])
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        // Get quote meta items, merge with quote data and return as array.
        $data = [];
        $i = 0;
        foreach($quotes as $quote) {
            $meta = DB::connection('srv1')->table('dwh_market_data.measurement_meta')
                ->where('measurement_id', $quote['measurement_id'])
                ->get(['meta_key', 'meta_value'])
                ->map(function ($item) {
                    return (array) $item;
                })
                ->toArray();
            $data[$i]['id'] = $quote['measurement_id'];
            $data[$i]['retrieval_date'] = $quote['measurement_datetime'];
            $data[$i]['symbol'] = $quote['symbol'];
            foreach($meta as $datum) {
                $data[$i][$datum['meta_key']] = $datum['meta_value'];
            }
            $i++;
        }

        // If there is a response, process it.
        if(!empty($data)) {

            // Flatten the response, so that we're guaranteed there's only one response.
            $data = $data[0];

            // Convert timestamp to date in YYYY-MM-DD format.
            try {
                $trade_date = IexApi::miliseconds_to_date($data['lastTradeTime']); // Convert timestamp to date in YYYY-MM-DD format.
            } catch (\Exception $e) {
                throw new QuoteRetrieveException("Error converting timestamp to date for " . $symbol . " quote: " . $e->getMessage() . ". Full dataset: " . json_encode($data));
            }

            // Create a new StockQuote object and fill it with the data from the IEX API.
            $stock_quote = new StockQuote();
            $stock_quote->date = $trade_date ?: null;
            $stock_quote->symbol = $data['symbol'];
            $stock_quote->http_source_id = HttpSource::find_by_reference("ptc_srv1")->id;
            $stock_quote->average_total_volume = is_numeric($data['avgTotalVolume']) ? floor($data['avgTotalVolume']) : null;
            $stock_quote->volume = is_numeric($data['volume']) ? floor($data['volume']) : null;
            $stock_quote->change = is_numeric($data['change']) ? round($data['change'], 3) : null;
            $stock_quote->change_percentage = is_numeric($data['changePercent']) ? round($data['changePercent'], 2) : null;
            $stock_quote->change_ytd = is_numeric($data['ytdChange']) ? round($data['ytdChange'], 3) : null;
            $stock_quote->open = is_numeric($data['iexOpen']) ? round($data['iexOpen'], 3) : null;
            $stock_quote->close = is_numeric($data['iexClose']) ? round($data['iexClose'], 3) : null;
            $stock_quote->company_name = isset($data['companyName']) ? $data['companyName'] : null;
            $stock_quote->market_cap = is_numeric($data['marketCap']) ? floor($data['marketCap']) : null;
            $stock_quote->pe_ratio = is_numeric($data['peRatio']) ? round($data['peRatio'], 3) : null;
            $stock_quote->week_52_low = is_numeric($data['week52Low']) ? round($data['week52Low'], 3) : null;
            $stock_quote->week_52_high = is_numeric($data['week52High']) ? round($data['week52High'], 3) : null;
            $stock_quote->metadata = $data;

            // Return StockQuote.
            return $stock_quote;
        } else {
            return null;
        }
    }



    public function import_another_day() {

        // LET OP: HIER TWEE AANPAKKEN HANTEREN:
        // 1. IMPORTEER ALLES VANUIT SRV1.
        // 2. IMPORTEER ALLE SYMBOLS VAN 24-11-2023 OF WHATEVER.
        // IN DIE VOLGORDE. ALLEBEI.
        // MOGELIJK MOETEN DIT DAN TWEE APARTE FUNCTIES WORDEN.

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

        // If requested StockQuote isn't registered yet under either (1) iex_prd, or (2) ptc_srv1.
        if(!StockQuote::exists($date, $symbol, "iex_prd") && !StockQuote::exists($date, $symbol, "ptc_srv1")) {
            $quote = $this->get_quote($symbol, $date);

            // Only if a quote was found, start the insert process
            if($quote) {

                // Check if the last_trade_time (in case of today quote) or ... (in case of historic quote) is equal to provided $date. If not, don't save the StockQuote.
                if($quote->date == $date) {
                    try {
                        $quote->save(); // If all is well, save the quote to the database.
                        return $quote; // Return the quote.
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


    public function get_quote($symbol, $date) {

        // Convert provided date to YYYY-MM-DD format, otherwise set to null.
        $date = date('Y-m-d', strtotime($date));

        // Break the program if given date is in the weekend.
        if(DatesHelper::is_weekend($date)) {
            throw new QuoteRetrieveException("StockQuote for " . $symbol . " could not be retrieved. Provided date " . $date . " is a weekend day. There is no trading in the weekend.");
        }

        // First, attempt to retrieve from SRV1. If that fails, get the historic quote from IEX.
//        $quote = $this->get_srv1_quote($symbol, $date); // Nope.
        $quote = null;

        if($quote) {
            return $quote;
        } else {
            $iex_api = new IexApi();
            $quote = $iex_api->get_historic_quote($symbol, $date);
            return $quote;
        }
    }
}
