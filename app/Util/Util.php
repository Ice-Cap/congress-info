<?php

namespace App\Util;

class Util
{
    public static function toObject(array $val): \stdClass | null | array
    {
        return json_decode(json_encode($val));
    }
}
