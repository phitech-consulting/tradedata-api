<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\DecompressAndJsonCast;

class IexHistoricSymbolSet extends Model
{
    use HasFactory;

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
