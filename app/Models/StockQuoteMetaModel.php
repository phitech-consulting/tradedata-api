<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockQuoteMetaModel extends Model
{
    use HasFactory;

    protected $table = 'stock_quotes_meta';

    public $fillable = [
        'stock_quote_id',
        'meta_key',
        'meta_value',
    ];


    public function stock_quote() {
        return $this->hasOne(StockQuoteModel::class);
    }
}
