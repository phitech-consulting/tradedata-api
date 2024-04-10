<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricReferenceModel extends Model
{
    use HasFactory;

    protected $table = 'historic_references';

    protected $fillable = [
        'symbol',
        'date',
        'diff_perc_close',
        'diff_perc_open',
        'diff_perc_change',
        'diff_change_perc',
        'historic_quote',
    ];

    protected $casts = [
        'historic_quote' => 'array',
    ];
}
