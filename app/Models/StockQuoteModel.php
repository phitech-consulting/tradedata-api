<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockQuoteModel extends Model
{
    use HasFactory;

    public $fillable = [
        'date',
        'type',
        'source',
        'symbol_id',
    ];


    public function exchange_product_model() {
        return $this->hasOne(ExchangeProductModel::class);
    }

    public function stock_quote_meta() {
        return $this->hasMany(StockQuoteMetaModel::class);
    }
}
