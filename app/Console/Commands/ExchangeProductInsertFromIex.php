<?php

namespace App\Console\Commands;

use App\Classes\ExchangeProduct;
use App\Models\ErrorLogModel;
use App\Models\IexHistoricSymbolSetModel;
use Illuminate\Console\Command;

class ExchangeProductInsertFromIex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange_product:insert_iex_symbol_set {iex_symbol_set_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Select one IexSymbolSet and insert its records to exchange_products';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // Get the optional user provided symbol_set_id parameter.
        $iex_symbol_set_id = $this->argument('iex_symbol_set_id');

        /**
         * Optionally find one specific IexSymbolSet, then trigger the process to insert this (or latest) IexSymbolSet
         * to exchange_products table.
         */
        try {
            $symbol_set = IexHistoricSymbolSetModel::find($iex_symbol_set_id);
            $exchange_products = new ExchangeProduct();
            $exchange_products->insert_iex_symbol_set($symbol_set);
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
