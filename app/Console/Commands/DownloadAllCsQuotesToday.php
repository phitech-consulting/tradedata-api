<?php

namespace App\Console\Commands;

use App\Models\ErrorLogModel;
use Illuminate\Console\Command;
use App\Classes\IexApi;

class DownloadAllCsQuotesToday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iex:download_all_cs_quotes_today';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger download process for all Common Stock (cs) quotes today (uses same method as \'iex:download_by_type\')';

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
