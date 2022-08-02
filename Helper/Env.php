<?php

namespace Neoan\Helper;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

class Env
{
    private static ?self $instance = null;
    function __construct(string $path)
    {
        try{
            $dotenv = Dotenv::createMutable($path);
            $dotenv->load();
        } catch (InvalidPathException $pathException) {
            //TODO: log
        }

    }
    public static function initialize(string $path = null): ?Env
    {
        if(self::$instance === null){
            self::$instance = new Env($path);
        }
        return self::$instance;
    }
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}