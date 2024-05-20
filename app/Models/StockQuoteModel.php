<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockQuoteModel extends Model
{
    use HasFactory;

    protected $table = 'stock_quotes';

    protected $fillable = [
        'date',
        'symbol',
        'http_source_id',
        'average_total_volume',
        'volume',
        'change',
        'change_percentage',
        'change_ytd',
        'open',
        'close',
        'company_name',
        'market_cap',
        'pe_ratio',
        'week_52_low',
        'week_52_high',
        'last_trade_time',
        'metadata',
        'type',
        'sector',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];


    /**
     * @return HasOne
     */
    public function exchange_product_model() {
        return $this->hasOne(ExchangeProductModel::class);
    }
}
