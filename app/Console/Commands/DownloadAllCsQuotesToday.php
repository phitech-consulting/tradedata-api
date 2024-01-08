<?php

namespace App\Console\Commands;

use App\Models\ErrorLogModel;
use Illuminate\Console\Command;
use App\Classes\StockQuote;

class DownloadAllCsQuotesToday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock_quote:download_all_cs_quotes_today';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $iex_api = new IexApi();
            echo $iex_api->download_by_type("cs");
            return Command::SUCCESS;
        } catch(\Exception $e) {
            ErrorLogModel::create([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}
