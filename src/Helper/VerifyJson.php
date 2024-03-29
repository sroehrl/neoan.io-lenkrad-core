<?php

namespace Neoan\Helper;


use JsonException;
use JsonSerializable;

class VerifyJson implements JsonSerializable
{
    private mixed $data;

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    static function isJson(string $string, $depth = 512, $flags = 0): bool
    {
        try {
            json_decode($string, false, $depth, $flags | JSON_THROW_ON_ERROR);
            return true;
        } catch (JsonException $e) {
            return false;
        }
    }

    /**
     * @throws JsonException
     */
    public function jsonSerialize(): string
    {
        try{
            $final = new DataNormalization($this->data);
        } catch (\TypeError $e) {
            http_response_code(500);
            return json_encode(['error' => 'output-data is not serializable']);
        }
        return json_encode($final->converted, JSON_THROW_ON_ERROR);
    }

}