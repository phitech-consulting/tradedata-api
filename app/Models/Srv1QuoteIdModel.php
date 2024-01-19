<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Srv1QuoteIdModel extends Model
{
    use HasFactory;

    protected $table = 'srv1_quote_ids';

    protected $fillable = ['done'];

    public $timestamps = false;
}
