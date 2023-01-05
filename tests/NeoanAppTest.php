<?php

namespace Test;

use Neoan\Helper\Setup;
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
        $setup = new Setup();
        $testApp = new NeoanApp($setup->setLibraryPath(__DIR__)->setPublicPath(__DIR__));
        $listenable = new Listenable();
        $testApp->invoke($listenable);
        $this->assertTrue(isset($testApp->testVariable));
    }
    function testGetInstance()
    {
        $setup = new Setup();
        $testApp = new NeoanApp($setup->setLibraryPath(__DIR__)->setPublicPath(__DIR__));
        $this->assertInstanceOf(NeoanApp::class, NeoanApp::getInstance());
    }

    function testSetProviderAndInvoke()
    {
        $setup = new Setup();
        $testApp = new NeoanApp($setup->setLibraryPath(__DIR__)->setPublicPath(__DIR__));
        $testApp->setProvider(new MockProvider());
        $this->assertInstanceOf(MockProvider::class, $testApp->injectionProvider);
        $this->assertInstanceOf(NeoanApp::class, $testApp());
    }

    function testSetupIncomplete()
    {
        $this->expectException(\Exception::class);
        new NeoanApp(new Setup());
    }

}

