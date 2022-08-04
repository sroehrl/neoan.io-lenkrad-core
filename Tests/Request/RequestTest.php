<?php

namespace Test\Request;

use Neoan\CoreInterfaces\RequestInterface;
use Neoan\NeoanApp;
use Neoan\Request\Request;
use PHPUnit\Framework\TestCase;
use Test\Mocks\MockRequest;

class RequestTest extends TestCase
{
    function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';
        $_SERVER['HTTP_USER_AGENT'] = 'mock-user-agent';
        $_FILES = ['file1'=> []];

    }
    function testMockable()
    {
        $r = Request::getInstance(new MockRequest());
        $this->assertInstanceOf(MockRequest::class, $r);
    }
    function testInitialization()
    {
        Request::detachInstance();
        $r = Request::getInstance();
        $this->assertInstanceOf(RequestInterface::class, $r);
        // full
        $app = new NeoanApp(__DIR__,__DIR__);
        $r($app);
        $this->assertSame('home', $r->requestUri);

    }
    function testInput()
    {
        //post
        $_POST = ['name' =>'david'];
        $this->init();
        $this->assertSame(Request::getInput('name'), 'david');
        $this->assertIsArray(Request::getInputs());
    }
    function testParameter()
    {

        $this->init();
        Request::setParameters(['all'=>'in']);
        $this->assertSame(Request::getParameter('all'), 'in');
        $this->assertIsArray(Request::getParameters());
    }
    function testFileOutputGeneric()
    {
        $_SERVER['REQUEST_URI'] = '/test.txt';
        $this->expectOutputString('test-me');
        $this->expectErrorMessage('Wanted to exit');
        $this->init();
    }

    private function init()
    {
        Request::detachInstance();
        $r = Request::getInstance();
        $app = new NeoanApp(__DIR__,__DIR__);
        $r($app);
    }

}
