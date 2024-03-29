<?php

namespace App\Console\Commands;

use App\Classes\IexApi;
use App\Models\ErrorLogModel;
use Illuminate\Console\Command;

class DownloadSymbols extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iex:download_symbols';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger download process of all IEX symbols today';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        /**
         * Trigger download process of all symbols via IEX API. Retrieve symbols (about 12000) and then insert them
         * to iex_historic_symbol_sets.
         */
        try {
            $iex = new IexApi();
            $iex->download_symbol_set();
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
