<?php

namespace Neoan\Helper;


class VerifyJson
{
    private mixed $data;
    public function __construct(mixed $data)
    {
        $this->data = $data;
    }
    static function isJson(string $string) :bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}