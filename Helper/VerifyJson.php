<?php

namespace Neoan\Helper;


class VerifyJson
{

    static function isJson(string $string) :bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

}