<?php

namespace app\Classes;

use App\Models\ErrorLogModel;
use App\Models\ExchangeProductModel;
use App\Models\IexHistoricSymbolSetModel;
use Illuminate\Support\Facades\DB;
use App\Exceptions\DataValidationException;
use Carbon\Carbon;

class ExchangeProduct extends ExchangeProductModel
{


    /**
     * Upsert IexSymbolSet to exchange_products table. Deduplication based on FIGI.
     * @param array $symbol_set
     * @param array $duplicate_figis
     * @return true
     * @throws DataValidationException
     */
    public function upsert_iex_symbol_set(IexHistoricSymbolSetModel $iex_symbol_set = null) {

        // If no SymbolSet was provided, just take the latest.
        if($iex_symbol_set == null) {
            $iex_symbol_set = IexHistoricSymbolSetModel::latest('date')->first();
        }

        // If there is anything to process, continue.
        if($iex_symbol_set) {
            // Use a transaction so that on any error, the entire transaction can be rolled back.
            DB::beginTransaction();

            try {

                // Get an array containing all columns of exchange_products table.
                $columns = ['symbol', 'exchange', 'exchangeSuffix', 'exchangeName', 'exchangeSegment', 'exchangeSegmentName', 'name', 'date', 'type', 'iexId', 'region', 'currency', 'isEnabled', 'figi', 'cik', 'lei'];

                // Loop through inputted array. Validate and upsert.
                foreach ($iex_symbol_set->symbols as $symbol) {

                    $values = []; // Reset values array.
                    $diff = array_diff(array_keys($symbol), $columns); // Get the difference between columns and current array element.

                    // If keys in current array element are not in the database, throw exception.
                    if (!empty($diff)) {
                        throw new DataValidationException("Validation Error: Columns not found - " . implode(', ', $diff) . " in input array. Likely something has changed in the IEX API and now the definition must be changed in App\Classes\EchangeProduct->upsert_iex_symbol_set().");
                    }

                    $values = [
                        'symbol' => $symbol['symbol'] ?? "",
                        'exchange' => $symbol['exchange'] ?? "",
                        'exchange_suffix' => $symbol['exchangeSuffix'] ?? "",
                        'exchange_name' => $symbol['exchangeName'] ?? "",
                        'exchange_segment' => $symbol['exchangeSegment'] ?? "",
                        'exchange_segment_name' => $symbol['exchangeSegmentName'] ?? "",
                        'name' => $symbol['name'] ?? "",
                        'date' => $symbol['date'] ?? "",
                        'type' => $symbol['type'] ?? "",
                        'iex_id' => $symbol['iexId'] ?? "",
                        'region' => $symbol['region'] ?? "",
                        'currency' => $symbol['currency'] ?? "",
                        'is_enabled' => $symbol['isEnabled'] ?? "",
                        'figi' => $symbol['figi'] ?? "",
                        'cik' => $symbol['cik'] ?? "",
                        'lei' => $symbol['lei'] ?? ""
                    ];

                    // If all goes well, upsert the current array element into the database.
                    if (!array_key_exists($values['figi'], $iex_symbol_set->duplicate_figis)) { // Don't insert any ExchangeProducts with duplicate FIGIs.
                        ExchangeProductModel::updateOrCreate(
                            ['figi' => $values['figi']], // Check for existing record using 'id' as the unique identifier.
                            $values // Data to insert or update.
                        );
                    }
                }

                // All went well, finalise the database operation.
                DB::commit();
                return true;

            } catch (DataValidationException $e) {

                // Something went wrong, roll back the transaction
                DB::rollBack();

                // Simply rethrow the exception.
                throw $e;
            }
        } else {

            // There were no operations at all. Return false.
            return false;
        }
    }



    /**
     * Enter a type (for instance 'cs' common stock) and get a collection of symbols back from DB.
     * @param string $type
     * @return mixed
     */
    public function get_all_by_type(string $type, string $date = null) {
        $date = $date ?? date('Y-m-d', strtotime('now'));
        $symbols = DB::table('iex_symbols')->select('symbol', 'id')->where('type', $type)->limit(4)->get();
        return $symbols;
    }

}
