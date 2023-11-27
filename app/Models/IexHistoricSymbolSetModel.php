<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\DecompressAndJsonCast;

class IexHistoricSymbolSetModel extends Model
{
    use HasFactory;

    protected $table = 'iex_historic_symbol_sets';


    public $fillable = [
        'date',
        'metadata',
        'duplicate_figis',
        'symbols',
    ];


    protected $casts = [
        'metadata' => 'array',
        'duplicate_figis' => DecompressAndJsonCast::class,
        'symbols' => DecompressAndJsonCast::class,
    ];
}
