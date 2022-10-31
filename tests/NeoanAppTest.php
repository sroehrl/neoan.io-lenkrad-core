<?php

namespace Test;

use Neoan\NeoanApp;
use PHPUnit\Framework\TestCase;
use Test\Mocks\Listenable;
use Test\Mocks\MockProvider;

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

    function testSetProviderAndInvoke()
    {
        $testApp = new NeoanApp(__DIR__, __DIR__);
        $testApp->setProvider(new MockProvider());
        $this->assertInstanceOf(MockProvider::class, $testApp->injectionProvider);
        $this->assertInstanceOf(NeoanApp::class, $testApp());
    }

}

