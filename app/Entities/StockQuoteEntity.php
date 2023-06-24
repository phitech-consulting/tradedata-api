<?php

namespace App\Entities;

use Illuminate\Support\Facades\DB;

class StockQuoteEntity extends Entity
{
    public $definition = [
        'entity_name' => 'stock_quote',
        'main_table' => 'stock_quotes',
        'meta_table' => 'stock_quotes_meta',
        'meta_instance_id_column' => 'stock_quote_id',
    ];

    public function __construct() {

    }
}
