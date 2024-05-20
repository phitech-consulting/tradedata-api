<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClosedDayModel extends Model
{
    use HasFactory;

    protected $table = 'closed_days';

    protected $fillable = ['date'];
}
