<?php

namespace Neoan\Enums;

enum RequestMethod
{
    case GET;
    case POST;
    case PUT;
    case PATCH;
    case DELETE;
    case OPTIONS;
    case HEAD;
    case CONNECT;
    case TRACE;
    public static function find(string $method): self
    {
        foreach (self::cases() as $case) {
            if($case->name === $method){
                return $case;
            }
        }
        return self::GET;
    }
}