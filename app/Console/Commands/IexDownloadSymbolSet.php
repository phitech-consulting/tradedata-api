<?php

namespace App\Console\Commands;

use App\Classes\IexApi;
use App\Models\ErrorLogModel;
use Illuminate\Console\Command;

class IexDownloadSymbolSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iex:download_symbol_set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve all symbols from IEX API and store into iex_historic_symbol_sets table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        /**
         * Retrieve all symbols from IEX API, compress and then store into iex_historic_symbol_sets table.
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
