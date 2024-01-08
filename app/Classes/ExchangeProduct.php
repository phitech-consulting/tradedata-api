<?php

namespace app\Classes;

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
                        'lei' => $symbol['lei'] ?? "",
                    ];

                    // If all goes well, upsert the current array element into the database.
                    if (!array_key_exists($values['figi'], $iex_symbol_set->duplicate_figis)) { // Don't insert any ExchangeProducts with duplicate FIGIs.
                        ExchangeProductModel::updateOrCreate(
                            ['figi' => $values['figi']], // Check for existing record using 'id' as the unique identifier.
                            $values // Data to insert or update.
                        );
                    }
                }

                // The statement below composes an array of all FIGIs from the IexSymbolSet.
                $figis = collect($iex_symbol_set->symbols)->pluck('figi')->map('strval')->toArray();

                // The statement below sets all ExchangeProducts to inactive that are not in the $figis array.
                ExchangeProductModel::whereNotIn('figi', $figis)
                    ->update(['active' => 0]);

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
     * Enter a type (for instance 'cs' common stock) and get a collection of symbols back from DB. If no $date is
     * given, the collection of symbols is retrieved from the exchange_products table. If $date is given, the
     * collection of symbols is retrieved from the IexHistoricSymbolSetModel table. But if there is no
     * IexHistoricSymbolSetModel for the given date, the collection of symbols is (still) retrieved from the
     * exchange_products table.
     * @param string $type
     * @param string|null $date
     * @return Collection
     */
    public function get_all_by_type(string $type, string $date = null) {

        // If date is given, convert to YYYY-MM-DD format, otherwise set to null.
        $date = $date ? date('Y-m-d', strtotime($date)) : null;

        // If date is given, try to get HistoricIexSymbolSet where date is given date
        if($date) {

            // Try to get HistoricIexSymbolSet where date is given date.
            $iex_symbol_set = IexHistoricSymbolSetModel::where('date', $date)->first();
            if($iex_symbol_set) {

                // If HistoricIexSymbolSet was found, get the symbols from the set and decompress.
                $symbols = $iex_symbol_set->symbols;

                /**
                 * Below code composes a collection of symbols from $symbols array where type is given type. The
                 * contents of the $symbols array is an array of arrays, where each array contains a symbol. The array
                 * of symbols is filtered by type, and then the filtered array is returned.
                 */

                $exchange_products = collect(array_filter($symbols, function($symbol) use ($type) {
                    return $symbol['type'] == $type;
                }))->map(function ($symbol) {
                    return new ExchangeProduct([
                        'symbol' => $symbol['symbol'], // Replace with actual property names
                        'exchange' => $symbol['exchange'],
                        'exchange_suffix' => $symbol['exchangeSuffix'],
                        'exchange_name' => $symbol['exchangeName'],
                        'exchange_segment' => $symbol['exchangeSegment'],
                        'exchange_segment_name' => $symbol['exchangeSegmentName'],
                        'name' => $symbol['name'],
                        'date' => $symbol['date'],
                        'type' => $symbol['type'],
                        'iex_id' => $symbol['iexId'],
                        'region' => $symbol['region'],
                        'currency' => $symbol['currency'],
                        'is_enabled' => $symbol['isEnabled'],
                        'figi' => $symbol['figi'],
                        'cik' => $symbol['cik'],
                        'lei' => $symbol['lei'],
                    ]);
                });

                // The line below returns the exchange products as a collection.
                return collect($exchange_products);
            }
        }

        // Get all columns from exchange_products table consistent with the provided $type.
        return ExchangeProductModel::where('type', $type)->get();
    }
}
