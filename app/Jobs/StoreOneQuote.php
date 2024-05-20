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
    protected $use_last_trading_day;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $symbol, bool $use_last_trading_day = null)
    {
        $this->symbol = $symbol;
        $this->use_last_trading_day = $use_last_trading_day;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $iex_api = new IexApi();
        $iex_api->store_one_quote($this->symbol, $this->use_last_trading_day);
    }
}
