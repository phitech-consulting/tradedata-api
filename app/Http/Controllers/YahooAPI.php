<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Scheb\YahooFinanceApi\ApiClient;
use Scheb\YahooFinanceApi\ApiClientFactory;
use GuzzleHttp\Client;

class YahooAPI extends Controller
{

    /**
     * @return mixed
     */
    public function create_yahoo_client () {

        /* Create a new client from the factory */
        $client = ApiClientFactory::createApiClient();

        /* Or use your own Guzzle client and pass it in */
        $options = [/*...*/];
        $guzzleClient = new Client($options);
        $client = ApiClientFactory::createApiClient($guzzleClient);

        /* Return the client object */
        return $client;
    }


    /**
     * @param $symbols
     * @return array
     */
    public function yahoo_get_key_indicators($symbols) {

        /* Initialise variables */
        $data = array();
        $result = array();

        /* Initiate Yahoo Client */
        $client = $this->create_yahoo_client();

        /* Retrieve data */
        $result['quotes'] = $client->getQuotes($symbols);


        foreach ($result['quotes'] as $quote) {

            $data['quotes'][] = array (
                'symbol' => array('label' => 'Symbol', 'value' => $quote->getSymbol()),
                'currency' => array('label' => 'Currency', 'value' => $quote->getCurrency()),
                'market' => array('label' => 'Market', 'value' => $quote->getMarket()),
                'exchange' => array('label' => 'Exchange', 'value' => $quote->getExchange()),
                'fullexchangename' => array('label' => 'Full exchange name', 'value' => $quote->getFullExchangeName()),
                'shortname' => array('label' => 'Short name', 'value' => $quote->getShortName()),
                'longname' => array('label' => 'Long name', 'value' => $quote->getLongName()),
                'sharesoutstanding' => array('label' => 'Shares outstanding', 'value' => $quote->getSharesOutstanding()),
                'marketcap' => array('label' => 'Market capitalization', 'value' => $quote->getMarketCap()),
                'trailingpe' => array('label' => 'Trailing PE', 'value' => $quote->getTrailingPE()),
                'forwardPE' => array('label' => 'Forward PE', 'value' => $quote->getForwardPE()),
                'epstrailing12m' => array('label' => 'EPS (traling 12 months)', 'value' => $quote->getEpsTrailingTwelveMonths()),
                'epsforward' => array('label' => 'EPS (forward)', 'value' => $quote->getEpsForward()),
                'ask' => array('label' => 'Ask', 'value' => $quote->getAsk()),
                'asksize' => array('label' => 'Ask volume', 'value' => $quote->getAskSize()),
                'bid' => array('label' => 'Bid', 'value' => $quote->getBid()),
                'bidsize' => array('label' => 'Bid volume', 'value' => $quote->getBidSize()),
                'avgDailyVolume10d' => array('label' => '10 Day average daily volume', 'value' => $quote->getAverageDailyVolume10Day()),
                'avgDailyVolume3m' => array('label' => '3 Month average daily volume', 'value' => $quote->getAverageDailyVolume3Month()),
                'bookvalue' => array('label' => 'Book value', 'value' => $quote->getBookValue()),
                'avg50d' => array('label' => '50 Day average', 'value' => $quote->getFiftyDayAverage()),
                'avg50dchange' => array('label' => '50 Day net change', 'value' => $quote->getFiftyDayAverageChange()),
                'avg50dchangepercent' => array('label' => '50 Day percent change', 'value' => $quote->getFiftyDayAverageChangePercent()),
                'avg200d' => array('label' => '200 Day average', 'value' => $quote->getTwoHundredDayAverage()),
                'avg200dchange' => array('label' => '200 Day net change', 'value' => $quote->getTwoHundredDayAverageChange()),
                'avg200dchangepercent' => array('label' => '200 Day percent change', 'value' => $quote->getTwoHundredDayAverageChangePercent()),
                'high52w' => array('label' => '52 Week high', 'value' => $quote->getFiftyTwoWeekHigh()),
                'high52wchange' => array('label' => '52 Week high net change', 'value' => $quote->getFiftyTwoWeekHighChange()),
                'high52wchangepercent' => array('label' => '52 Week high percent change', 'value' => $quote->getFiftyTwoWeekHighChangePercent()),
                'low52w' => array('label' => '52 Week low', 'value' => $quote->getFiftyTwoWeekLow()),
                'low52wchange' => array('label' => '52 Week low net change', 'value' => $quote->getFiftyTwoWeekLowChange()),
                'low52wchangepercent' => array('label' => '52 Week low percent change', 'value' => $quote->getFiftyTwoWeekLowChangePercent()),
                'trailingannualdividendrate' => array('label' => 'Trailing annual dividend rate', 'value' => $quote->getTrailingAnnualDividendRate()),
                'trailingannualdividendyield' => array('label' => 'Trailing annual dividend yield', 'value' => $quote->getTrailingAnnualDividendYield()),
                'marketstate' => array('label' => 'Current market state', 'value' => $quote->getMarketState())
            );

        }

        /* Return the retrieved data */
        return $data;

    }


    /**
     * @param $query
     * @return array
     */
    public function yahoo_get_symbols($query) {

        /* Initialise variables */
        $data = array();
        $result = array();

        /* Initiate Yahoo Client */
        $client = $this->create_yahoo_client();

        /* Retrieve data */
        $result = $client->search($query);

        /* Transform data */
        foreach($result as $item) {
//            $data[] = $item->SearchResult();
        }

        /* Return the retrieved data */
        return $result;
    }










    public function output_yahoo_page_test() {

        // Create a new client from the factory
        $client = ApiClientFactory::createApiClient();

        // Or use your own Guzzle client and pass it in
        $options = [/*...*/];
        $guzzleClient = new Client($options);
        $client = ApiClientFactory::createApiClient($guzzleClient);


        $data = array (
            'title' => 'Output Yahoo Page',
            'paragraph' => 'This is an output page for Yahoo data.'
        );


        // Returns an array of Scheb\YahooFinanceApi\Results\SearchResult
        /* This is a search where one string is inputted and out come stock symbols that correspond in some way. */
        //$data['searchResult'] = print_r($client->search("App"), true);

        // Returns an array of Scheb\YahooFinanceApi\Results\HistoricalData
        /* This returns high/low and close prices of stocks */
        //$data['historicalData'] = print_r($client->getHistoricalQuoteData("AD.AS", ApiClient::INTERVAL_1_DAY, new \DateTime("-14 days"), new \DateTime("today")), true);

        // Or you can filter by dividends and return an array of Scheb\YahooFinanceApi\Results\DividendData
//        $data['dividendData'] = print_r($client->getHistoricalDividendData("AAPL", new \DateTime("-14 days"), new \DateTime("today")), true);

        // Or you can filter by splits and return an array of Scheb\YahooFinanceApi\Results\SplitData
//        $data['splitData'] = print_r($client->getHistoricalSplitData("AAPL", new \DateTime("-300 days"), new \DateTime("today")), true);

        // Returns Scheb\YahooFinanceApi\Results\Quote
        /* Returns Currency Exchange prices */
//        $data['exchangeRate'] = print_r($client->getExchangeRate("USD", "EUR"), true);

        // Returns an array of Scheb\YahooFinanceApi\Results\Quote
//        $data['exchangeRates'] = print_r($client->getExchangeRates([
//            ["USD", "EUR"],
//            ["EUR", "USD"],
//        ]), true);

        // Returns Scheb\YahooFinanceApi\Results\Quote
        /* Returns current stock quote, including EPS, outstanding shares and other key parameters */
//        $data['quote'] = print_r($client->getQuote("AAPL"), true);

        // Returns an array of Scheb\YahooFinanceApi\Results\Quote
        /* Same as previous, but with multiple at same time */
        //$data['quotes'] = print_r($client->getQuotes(["AAPL", "GOOG"]), true);

        /* Output view with data array */
        return view ('pages.OutputYahooTest')->with($data);

    }
}
