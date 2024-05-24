<?php

namespace app\Classes;

use App\Models\ExchangeProductModel;
use App\Models\IexHistoricSymbolSetModel;
use Illuminate\Support\Facades\DB;
use \Exception;
use Carbon\Carbon;

class ExchangeProduct extends ExchangeProductModel
{

    /**
     * Upsert IexSymbolSet to exchange_products table.
     * @param IexHistoricSymbolSetModel|null $iex_symbol_set
     * @return bool
     * @throws Exception
     */
    public function insert_iex_symbol_set(IexHistoricSymbolSetModel $iex_symbol_set = null) {

        // If no SymbolSet was provided, just take the latest.
        if($iex_symbol_set == null) {
            $iex_symbol_set = IexHistoricSymbolSetModel::latest('date')->first();
        }

        // If there is anything to process, continue.
        if($iex_symbol_set) {

            // Use a transaction so that on any error, the entire transaction can be rolled back.
            DB::beginTransaction();

            // Empty entire table to add fresh records.
            ExchangeProduct::truncate();

            try {

                // Loop through inputted array. Validate and upsert.
                foreach ($iex_symbol_set->symbols as $symbol) {

                    $values = []; // Reset values array.

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
                    ExchangeProductModel::create(
                        $values // Data to insert.
                    );
                }

                // All went well, finalise the database operation.
                DB::commit();
                return true;

            } catch (Exception $e) {

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
     * Get a collection of all symbols from DB. If no $date is given, the collection of symbols is retrieved from the
     * exchange_products table. If $date is given, collection of symbols is retrieved from IexHistoricSymbolSetModel
     * table. If there is no IexHistoricSymbolSetModel for the given date, null is returned.
     * @param string|null $date
     * @return Collection|null
     */
    public function get_all(string $date = null) {

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

                $exchange_products = collect($symbols)->map(function ($symbol) {
                    return new ExchangeProduct([
                        'symbol' => $symbol['symbol'], // Replace with actual property names
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
                    ]);
                });

                // The line below returns the exchange products as a collection.
                return collect($exchange_products);

            } else {
                return null; // If no symbol set was found for this date, return null.
            }
        }

        // Get all columns from exchange_products table.
        return ExchangeProductModel::get();
    }


    /**
     * Helper method to retrieve just the name, given a symbol.
     * @param $symbol
     * @return ExchangeProduct|null
     */
    public static function get_name_by_symbol($symbol) {
        return self::where('symbol', $symbol)->first()->name ?? null;
    }
}
