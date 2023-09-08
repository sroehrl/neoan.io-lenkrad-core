<?php

namespace Test\Cors;

use Neoan\Cors\Cors;
use PHPUnit\Framework\TestCase;

class CorsTest extends TestCase
{
    function testOrigin()
    {
        $cors = new Cors();
        $cors->addAllowedOrigin('*');
        $this->assertSame(['*'], $cors->getAllowedOrigins());
    }

    function testAllowedHeaders()
    {
        $cors = new Cors();
        $cors->setAllowedHeaders(['Onin']);
        $cors->addAllowedHeader('X-ANY');
        $this->assertContains('X-ANY', $cors->getAllowedHeaders());
    }

    function testAllowedMethods()
    {
        $cors = new Cors();
        $cors->setAllowedMethods(['POST', 'GET']);
        $cors->addAllowedMethod('PUT');
        $this->assertSame(['POST', 'GET', 'PUT'], $cors->getAllowedMethods());
    }

    function testOptionsCall()
    {
        $this->expectException(\Exception::class);
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $cors = new Cors();
        $cors->setAllowMethodOptions(false);
        $cors();

    }

    function testInvoke()
    {
        $cors = new Cors();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertInstanceOf(Cors::class, $cors());
    }

}
