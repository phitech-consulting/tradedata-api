<?php

namespace App\Classes;

use App\Exceptions\QuoteRetrieveException;
use App\Exceptions\QuoteStoreException;
use App\Jobs\ImportOneQuote;
use App\Models\Srv1QuoteIdModel;
use Illuminate\Support\Facades\DB;

class ImportSrv1Helper
{

    public static function import_srv1_quote_by_id($measurement) {

        // Get quotes and return as array.
        $quotes = DB::connection('srv1')->table('dwh_market_data.measurements')
            ->where('measurement_id', $measurement->id)
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
            $data[$i]['srv1_id'] = $quote['measurement_id'];
            $data[$i]['srv1_measurement_datetime'] = $quote['measurement_datetime'];
            $data[$i]['srv1_symbol'] = $quote['symbol'];
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

            // Only get from IEX API if not already exists yet in database, otherwise skip.
            if (!StockQuote::exists($stock_quote->date, $stock_quote->symbol, "iex_prd") && !StockQuote::exists($stock_quote->date, $stock_quote->symbol, "ptc_srv1")) {

                // Only if a quote was found, start the insert process
                if($stock_quote) {
                    try {
                        $measurement->done = 1;
                        $measurement->save();
                        $stock_quote->save(); // If all is well, save the quote to the database.
                        return $stock_quote; // Return the quote.
                    } catch(\Exception $e) {
                        throw new QuoteStoreException("StockQuote for symbol " . $stock_quote->symbol . " on date " . $stock_quote->date . " could not be stored because of error: " . $e->getMessage() . ". Full dataset: " . json_encode($stock_quote->toArray(), JSON_PRETTY_PRINT));
                    }

                } else {
                    throw new QuoteRetrieveException("No quote was retrieved from IEX API for symbol " . $stock_quote->symbol . " and date " . $stock_quote->date . ".");
                }

            } else {
                throw new QuoteStoreException("Skipped. StockQuote for symbol " . $stock_quote->symbol . ", on date " . $stock_quote->date ." already exists in database.");
                // Side-note: mind the fact that the US markets open at 09:30 (New York time), which is 15:30 in Amsterdam. That means that until this time, while in NL you might expect the StockQuote for today, IEX would still return the stock quote for yesterday. This might explain the case where you expect today's quote to be freshly added, but you're noticing (above) that it was skipped. Wait until 15:30h.
            }

        } else {
            return null;
        }

    }



    public static function import_1000() {

        // Get the next 100 quotes to import from srv1.
        $next1000Quotes = Srv1QuoteIdModel
            ::where('done', 0)
            ->orderBy('id', 'desc')
            ->limit(1000)
            ->get();

        // Set some parameters for the loop.
        $max_delay = self::get_delay_seconds(count($next1000Quotes)); // Get max delay in seconds for given number of requests.

        // Loop through all symbols and store one quote for each symbol.
        foreach($next1000Quotes as $quote) {

            // Dispatch job to queue that in turn calls the import_one_quote method.
            ImportOneQuote::dispatch($quote)->delay(now()->addSeconds(rand(0, $max_delay))); // Add random delay for rate limiting.
        }

        // Return a short description of the processes that were triggered.
        return ["count(next1000Quotes)" => count($next1000Quotes), "max_delay" => $max_delay];
    }


    public static function get_delay_seconds(int $number_of_requests, int $max_req = 30) {

        // Set rate levels.
        $rate_level_1 = 100;
        $rate_level_2 = 500;
        $rate_level_3 = 2000;
        $rate_level_4 = 5000;
        $rate_level_5 = 8000;
        $rate_level_6 = 12000;
        $rate_level_7 = 16000;
        $rate_level_8 = 20000;

        // Determine delay seconds based on number of simultaneous requests.
        switch ($number_of_requests) {
            case 0:
                throw new QuoteRetrieveException("There are no requests to be made.");
            case $number_of_requests < $rate_level_1:
                return intval($rate_level_1 / $max_req);
            case $number_of_requests < $rate_level_2:
                return intval($rate_level_2 / $max_req);
            case $number_of_requests < $rate_level_3:
                return intval($rate_level_3 / $max_req);
            case $number_of_requests < $rate_level_4:
                return intval($rate_level_4 / $max_req);
            case $number_of_requests < $rate_level_5:
                return intval($rate_level_5 / $max_req);
            case $number_of_requests < $rate_level_6:
                return intval($rate_level_6 / $max_req);
            case $number_of_requests < $rate_level_7:
                return intval($rate_level_7 / $max_req);
            case $number_of_requests <= $rate_level_8:
                return intval($rate_level_8 / $max_req);
            case $number_of_requests > $rate_level_8:
                throw new QuoteRetrieveException("Number of simultaneous requests is too high. You've got to find another way to do this.");
            default: // If no case matches, return 0.
                return 0;
        }
    }

}
