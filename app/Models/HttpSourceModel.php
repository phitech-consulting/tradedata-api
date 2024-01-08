<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HttpSourceModel extends Model
{
    use HasFactory;

    protected $table = 'http_sources';

    protected $fillable = ['reference', 'name', 'operator_id'];


    /**
     * @return BelongsTo
     */
    public function operator() {
        return $this->belongsTo(OperatorModel::class);
    }
}
