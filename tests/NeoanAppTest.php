<?php

namespace Test;

use Neoan\NeoanApp;
use PHPUnit\Framework\TestCase;
use Test\Mocks\Listenable;

class NeoanAppTest extends TestCase
{
    function testInitiation()
    {
        $_SERVER["SERVER_PROTOCOL"] = 'http';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $testApp = new NeoanApp(__DIR__, __DIR__);
        $listenable = new Listenable();
        $testApp->invoke($listenable);
        $this->assertTrue(isset($testApp->testVariable));
    }
    function testGetInstance()
    {
        $testApp = new NeoanApp(__DIR__, __DIR__);
        $this->assertInstanceOf(NeoanApp::class, NeoanApp::getInstance());
    }
    /*function testRun()
    {
        $testApp = new NeoanApp(__DIR__, __DIR__);
        $this->expectWarning();
        $this->expectException(\Exception::class);
        $testApp->run();

    }*/
}
