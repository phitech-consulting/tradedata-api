<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockQuote extends Model
{
    use HasFactory;

    public $fillable = [
        'date',
        'type',
        'source',
        'symbol_id',
    ];


    public function iex_symbol() {
        return $this->hasOne(IexSymbol::class);
    }

    public function stock_quote_meta() {
        return $this->hasMany(StockQuoteMeta::class);
    }
}
