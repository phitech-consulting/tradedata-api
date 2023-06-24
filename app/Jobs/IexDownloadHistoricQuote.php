<?php

namespace App\Jobs;

use App\Services\StockQuoteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IexDownloadHistoricQuote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $symbol = "";
    private $date = "";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($symbol, $date)
    {
        $this->symbol = $symbol;
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $quote_service = new StockQuoteService;
        $quote_service->download_quote($this->symbol, $this->date);

    }
}
