<?php

namespace App\Console\Commands;

use App\Classes\ImportIexHistoricHelper;
use App\Models\ErrorLogModel;
use Illuminate\Console\Command;

class ImportAnotherDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:another_day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import another day (one record in ImportPlan) StockQuotes from SRV1 and update the ledger';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $result = ImportIexHistoricHelper::import_another_day();
            print_r($result);
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
