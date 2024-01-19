<?php

namespace App\Console\Commands;

use App\Classes\ImportSrv1Helper;
use App\Models\ErrorLogModel;
use Illuminate\Console\Command;

class Import1000 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:1000';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import another 1000 StockQuotes from SRV1 and update the ledger';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            print_r(ImportSrv1Helper::import_1000());
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
