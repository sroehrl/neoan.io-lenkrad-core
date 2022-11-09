<?php

namespace Neoan\Enums;

enum RequestMethod
{
    case GET;
    case POST;
    case PUT;
    case PATCH;
    case DELETE;
    public static function find(string $method): self
    {
        foreach (self::cases() as $case) {
            if($case->name === $method){
                return $case;
            }
        }
    }
}