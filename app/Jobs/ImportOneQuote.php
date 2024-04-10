<?php

namespace App\Jobs;

use App\Classes\ImportIexHistoricHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportOneQuote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $symbol;
    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($symbol, $date) {
        $this->symbol = $symbol;
        $this->date = $date;
    }


    /**
     * Execute the job.
     * @return void
     * @throws \App\Exceptions\QuoteRetrieveException
     * @throws \App\Exceptions\QuoteStoreException
     */
    public function handle()
    {
        $import_helper = new ImportIexHistoricHelper();
        $import_helper->import_one_quote($this->symbol, $this->date);
    }
}
