<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeProductModel extends Model
{
    use HasFactory;

    protected $table = 'exchange_products';

    public $fillable = [
        'symbol',
        'exchange',
        'exchange_suffix',
        'exchange_name',
        'exchange_segment',
        'exchange_segment_name',
        'name',
        'date',
        'type',
        'iex_id',
        'region',
        'currency',
        'is_enabled',
        'figi',
        'cik',
        'lei',
        'active',
    ];


    /**
     * @return HasMany
     */
    public function stock_quotes() {
        return $this->hasMany(StockQuoteModel::class);
    }
}
