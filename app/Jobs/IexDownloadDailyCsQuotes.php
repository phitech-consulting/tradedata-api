<?php

namespace App\Jobs;

use App\Services\StockQuoteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IexDownloadDailyCsQuotes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private string $date) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $quote_service = new StockQuoteService;
        $quote_service->download_quotes_by_type('cs', $this->date);
    }
}
