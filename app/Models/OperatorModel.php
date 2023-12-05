<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorModel extends Model
{
    use HasFactory;

    protected $table = 'operators';

    protected $fillable = ['reference', 'name'];


    /**
     * @return HasMany
     */
    public function http_sources() {
        return $this->hasMany(HttpSourceModel::class);
    }
}
