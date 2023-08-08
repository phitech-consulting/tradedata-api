<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DecompressAndJsonCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        // Decode from base64, decompress, and then decode the JSON
        return json_decode(gzinflate(base64_decode($value)), true);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        // First, encode as JSON, then compress, and finally encode to base64
        return base64_encode(gzdeflate(json_encode($value), 1)); // On such a small dataset (approx 5.7MB) it makes no sense using a higher compression level and paying the huge calculation costs that come with it. Tested it. Levels 1 through 9 result in +- [550KB,780KB] after compression. Given that money-wise, calculation is more expensive than to disk space, keep compression level at 1.
    }
}
