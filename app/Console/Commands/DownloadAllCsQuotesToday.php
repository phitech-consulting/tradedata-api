<?php

namespace App\Console\Commands;

use App\Models\ErrorLogModel;
use Illuminate\Console\Command;

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
            $stock_quote_service = new StockQuote();
            echo $stock_quote_service->download_by_type("cs");
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
