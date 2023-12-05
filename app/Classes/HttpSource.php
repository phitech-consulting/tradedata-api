<?php

namespace app\Classes;

use App\Models\HttpSourceModel;

class HttpSource extends HttpSourceModel
{

    public static function find_by_reference($reference) {
        return HttpSource::where('reference', $reference)->first();
    }

}
