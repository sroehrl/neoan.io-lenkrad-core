<?php

namespace Test\Request;

use Neoan\Helper\Setup;
use Neoan\NeoanApp;
use Neoan\CoreInterfaces\RequestInterface;
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
        $setup = new Setup();
        $app = new NeoanApp($setup->setLibraryPath(__DIR__)->setPublicPath(__DIR__));
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
        $this->expectErrorMessage('Wanted to exit');
        $this->setOutputCallback(function($output){
            var_dump($output);
        });
        $this->init();
    }
    function testQueries()
    {
        $_SERVER['REQUEST_URI'] = '/home?some=value';
        $_SERVER['QUERY_STRING'] = 'some=value';
        $this->init();
        Request::setQueries([...Request::getQueries(),'another'=>'test']);
        $this->assertSame(2, count(Request::getQueries()));
        $this->assertSame('value', Request::getQuery('some'));
    }

    function testFiles()
    {
        $_FILES = ['file1'=> ['name'=>'file1.txt']];
        $this->init();
        $this->assertSame('file1.txt', Request::getFile('file1')['name']);
        $this->assertSame(1, count(Request::getFiles()));
    }

    private function init()
    {
        Request::detachInstance();
        $this->setOutputCallback(function($output){
            var_dump($output);
        });
        $r = Request::getInstance();
        $setup = new Setup();
        $app = new NeoanApp($setup->setLibraryPath(__DIR__)->setPublicPath(__DIR__), __DIR__);
        $r($app);
    }

}
