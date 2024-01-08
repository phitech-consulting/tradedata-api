<?php

namespace App\Jobs;

use App\Classes\IexApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreOneQuote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $symbol;
    protected $date;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $symbol, string $date = null)
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
        $iex_api = new IexApi();
        $iex_api->store_one_quote($this->symbol, $this->date);
    }
}
