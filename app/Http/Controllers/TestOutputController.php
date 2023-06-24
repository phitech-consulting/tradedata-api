<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Phitech\Entities\Entity;

class TestOutputController extends Controller
{
    public function index() {
        $pagespeed = new Entity('pagespeed_sample');
        $pagespeed_data = "";
//        $pagespeed_data .= "<p><pre>" . print_r($pagespeed->upsert_instance(['id' => 14], ["type" => "pagespeed-measure", "source" => "lighthouse", "web_page_sampling" => "1"], [["meta_key_1", "Anne", 2], ["meta_key_2", "Sjaak"], ["meta_key_3", "Ali"]]), true) . "</pre></p>";
//        $pagespeed_data .= "<p><pre>" . print_r($pagespeed->upsert_instance(['id' => 4], ["type" => "pagespeed-measure", "source" => "google", "web_page_sampling" => "1"], [["meta_key_1", "Adje"], ["meta_key_2", "Anne"], ["meta_key_3", "Arie"], ["meta_key_4", "Achmed"]]), true) . "</pre></p>";
        $pagespeed_data .= "<p><pre>" . print_r($pagespeed->upsert_instance([], ["type" => "pagespeed-measure", "source" => "google", "web_page_sampling" => "1"], [["meta_key_1", "Adje"], ["meta_key_2", "Anne"], ["meta_key_3", "Arie"], ["meta_key_4", "Achmed"]]), true) . "</pre></p>";
//        $pagespeed_data .= "<p><pre>" . print_r($pagespeed->get_entity_matrix([1,2]), true) . "</pre></p>";
        return $pagespeed_data;
    }


    public function download_quotes_cs() {
        $now = date("Ymd", strtotime(now()));
        $quote_service = new \App\Services\StockQuoteService();
        $quote_service->download_quotes_by_type('cs', $now);
    }
}
