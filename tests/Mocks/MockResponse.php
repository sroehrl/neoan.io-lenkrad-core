<?php

namespace Test\Mocks;

use Neoan\CoreInterfaces\ResponseInterface;
use Neoan\Render\Renderer;
use Neoan\Response\Response;

class MockResponse implements ResponseInterface
{
    public array $overrides;
    public array $responseHeaders = [];
    public string $defaultOutput = 'json';
    public string $defaultRenderer = Renderer::class;
    public static function __callStatic($name, $args)
    {
        if(!isset(self::$overrides[$name])){
            return Response::$name(...$args);
        } else {
            return self::$overrides[$name](...$args);
        }
    }

    public static function detachInstance(): void
    {
        // TODO: Implement detachInstance() method.
    }
}