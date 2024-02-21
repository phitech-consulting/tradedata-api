<?php

namespace app\Classes;

use App\Exceptions\QuoteRetrieveException;

class Srv1
{
    /**
     * Retrieves a stock quote for a given symbol and date.
     *
     * @param string $symbol The symbol of the stock.
     * @param string|null $date The date in YYYY-MM-DD format. If null, the current date will be used.
     * @return StockQuote|null The stock quote object for the given symbol and date, or null if no quote is found.
     * @throws QuoteRetrieveException If there is an error converting the timestamp to date or retrieving the quote.
     */
    public function get_quote($symbol, $date) {

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


    /**
     * Retrieves a stock quote for a given measurement ID.
     *
     * @param int $id The ID of the measurement.
     * @return StockQuote|null The stock quote object for the given measurement ID, or null if no quote is found.
     * @throws QuoteRetrieveException If there is an error converting the timestamp to date or retrieving the quote.
     */
    public function get_quote_by_measurement_id($id) {

        // If date is given, convert to YYYY-MM-DD format, otherwise set to null.
        $date = date('Y-m-d', strtotime($date));

        // Get quotes and return as array.
        $quotes = DB::connection('srv1')->table('dwh_market_data.measurements')
            ->where('measurement_id', $id)
            ->take(1)
            ->get(['measurement_id', 'measurement_datetime', 'symbol'])
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        dd($quotes);

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
                throw new QuoteRetrieveException("Error converting timestamp to date for " . $data['symbol'] . " quote: " . $e->getMessage() . ". Full dataset: " . json_encode($data));
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
}
