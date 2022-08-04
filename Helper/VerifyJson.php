<?php

namespace Neoan\Helper;


use Neoan\Model\Collection;
use Neoan\Model\Model;

class VerifyJson implements \JsonSerializable
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

    public function jsonSerialize(): string
    {
        $final = $this->data;
        if($final instanceof Collection || $final instanceof Model){
            $final =  $final->toArray();
        }

        return $this->makeJson($final);
    }
    private function makeJson($rawData): string
    {
        try{
            return json_encode(new VerifyJson($rawData),JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            http_response_code(500);
            return json_encode(['error' => 'output-data is not serializable']);
        }

    }
}