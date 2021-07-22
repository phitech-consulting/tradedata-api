<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Scheb\YahooFinanceApi\ApiClient;
use Scheb\YahooFinanceApi\ApiClientFactory;
use GuzzleHttp\Client;
use App\Http\Controllers\YahooAPI;

class GetYahooData extends Controller
{

    /**
     * @return mixed
     */
    public function output_yahoo_page_training () {
        $data = array (
            'title' => 'Output Yahoo Page!',
            'paragraph' => 'This is an output page for Yahoo data.',
            'services' => ['web design', 'SEO']
        );

        /* Return view including retrieved data */
        return view ('pages.OutputYahoo')->with($data); // Preferred, also works with arrays, as shown here
    }


    /**
     * @return mixed
     */
    public function output_watchlist_key_indicators () {

        /* Define symbols to get key indicators for */
        $symbols = [
            "DOGEF",
            "SNOW",
            "AKER",
            "NEL.OL",
            "ENR.DE",
            "BOX",
            "^AEX",
            "^GSPC",
            "TSLA",
            "ZM",
            "Snow.AS",
            "PNL.AS",
            "RAND.AS",
            "KPN.AS",
            "PHARM.AS",
            "AD.AS",
            "LIGHT.AS",
            "WWR",
            "RDSA.AS",
            "FAST.AS",
            "BRNL.AS",
            "APERAM.AS",
            "UN01.DE",
            "ORSTED.CO",
            "SGRE.MC",
            "ENPH",
            "FSLY",
            "BEP",
            "FSLR",
            "NEE"
        ];

        /* Retrieve data */
        $yahooClient = new YahooAPI;
        $data = $yahooClient->yahoo_get_key_indicators($symbols);

        /* Return view including retrieved data */
        return view ('pages.StocksKeyIndicators', compact('data'));
//        return view ('pages.StocksKeyIndicators')->with($data); // Preferred, also works with arrays, as shown here
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function process_post_trade_prices(Request $request) {

        $allshit = $request->all();
        foreach($allshit as $shit) {
            $symbols[] = $shit;
        }

        $yahooClient = new YahooAPI;
        $data = $yahooClient->yahoo_get_key_indicators($symbols);



        return response()->json(
            [
                'status' => '200',
                'data' => $data,
                'message' => 'success'
            ],
            200
        );

    }



    public function output_yahoo_symbols() {

        /**/
        $data = array();

        /**/
        return view ('pages.YahooSearchSymbols', compact('data'));

    }



    public function process_post_search_symbols(Request $request) {

        $arrRequestData = $request->all();
        foreach($arrRequestData as $requestdata) {
            $query = $requestdata;
        }

        $yahooClient = new YahooAPI;
        $data = $yahooClient->yahoo_get_symbols($query);

        return response()->json(
            [
                'status' => '200',
                'data' => $data,
                'message' => 'success'
            ],
            200
        );

    }

}
