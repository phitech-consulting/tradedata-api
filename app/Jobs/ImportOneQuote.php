<?php

namespace App\Jobs;

use App\Classes\ImportSrv1Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportOneQuote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public $measurement) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $import_helper = new ImportSrv1Helper();
            $import_helper->import_srv1_quote_by_id($this->measurement);
        } finally {
            $this->measurement->done = 1;
            $this->measurement->save();
        }
    }
}
