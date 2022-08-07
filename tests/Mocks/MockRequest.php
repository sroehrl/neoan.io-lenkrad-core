<?php

namespace Test\Mocks;

use Neoan\NeoanApp;
use Neoan\CoreInterfaces\RequestInterface;
use Neoan\Request\Request;

class MockRequest implements RequestInterface
{
    public static array $overrides;

    private static ?RequestInterface $instance = null;

    public string $requestMethod;
    public string $webPath;
    public array $requestHeaders = [];
    public array $urlParts = [];
    public array $queryParts = [];
    public array $parameters = [];
    public string $requestUri;
    public array $files = [];
    public array $input = [];

    public static function __callStatic($name, $args)
    {
        if(!isset(self::$overrides[$name])){
            return Request::$name(...$args);
        } else {
            return self::$overrides[$name](...$args);
        }
    }

    public static function detachInstance(): void
    {
        // TODO: Implement detachInstance() method.
    }

    public function __invoke(NeoanApp $app = null, $additional = null)
    {
        // TODO: Implement __invoke() method.
    }
}