<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportPlanModel extends Model
{
    use HasFactory;

    protected $table = 'import_plans';

    protected $fillable = ['date', 'callback', 'done', 'symbol_set'];
}
