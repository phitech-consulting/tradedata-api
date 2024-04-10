<?php

namespace App\Models;

use App\Casts\DecompressAndJsonCast;
use app\Classes\HttpSource;
use app\Classes\StockQuote;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IexHistoricStockQuoteModel extends Model
{
    use HasFactory;

    protected $table = 'iex_historic_stock_quotes';


    public $fillable = [
        'date',
        'symbol',
        'quote_data',
    ];

    protected $casts = [
        'quote_data' => 'array',
    ];


    /**
     * Method to determine whether stock quote for particular symbol on one date already exists in DB.
     * @param string $date
     * @param string $symbol
     * @return mixed
     */
    public static function exists(string $date, string $symbol) {

        // Convert date to YYYY-MM-DD format.
        $date = date('Y-m-d', strtotime($date));

        // Check if StockQuote exists for given date and symbol.
        return IexHistoricStockQuoteModel::where('date', $date)
            ->where('symbol', $symbol)
            ->exists();
    }
}
