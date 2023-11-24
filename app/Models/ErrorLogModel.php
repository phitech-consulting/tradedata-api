<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLogModel extends Model
{
    use HasFactory;

    protected $table = 'error_logs';

    protected $fillable = ['message', 'trace'];
}
