<?php

namespace App\Console\Commands;

use App\Models\ErrorLogModel;
use Illuminate\Console\Command;
use App\Classes\IexApi;

class IexDownloadAllQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iex:download_all_quotes {use_last_trading_day?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger download process for all quotes today, store into stock_quotes table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // Get the optional user provided symbol_set_id parameter.
        $use_last_trading_day = $this->argument('use_last_trading_day');

        // Convert the integer flag to a boolean value.
        $use_last_trading_day = $use_last_trading_day == 1;

        try {
            $iex_api = new IexApi();
            $process_data = $iex_api->download_all_quotes($use_last_trading_day);
            $this->line("\n<fg=green>" . print_r($process_data, true) . "</>");
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
