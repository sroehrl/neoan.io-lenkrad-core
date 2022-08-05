<?php

namespace Test\Response;

use Neoan\CoreInterfaces\ResponseInterface;
use Neoan\Enums\ResponseOutput;
use Neoan\Response\Response;
use PHPUnit\Framework\TestCase;
use Test\Mocks\MockRenderer;
use Test\Mocks\MockResponse;

class ResponseTest extends TestCase
{
    function testInvoke()
    {
        $r = new Response();
        $this->assertInstanceOf(ResponseInterface::class, $r());
    }
    function testMockability()
    {
        Response::getInstance(new MockResponse());
        $r = new Response();
        $this->assertInstanceOf(ResponseInterface::class, $r());
    }
    function testSetOutput()
    {
        Response::setDefaultOutput(ResponseOutput::HTML);
        $this->assertSame(Response::getDefaultOutput(),'html');
    }
    function testHtmlOutput()
    {
        Response::detachInstance();
        Response::setDefaultOutput(ResponseOutput::HTML);
        Response::setDefaultRenderer(MockRenderer::class);
        $this->expectErrorMessage('renderer');
        Response::html(['any']);
    }
    function testDefaultRendererSecurity()
    {
        $this->expectErrorMessage('Renderer not compatible!');
        Response::setDefaultRenderer(NotARenderer::class);
    }
    function testJson()
    {
        $this->expectErrorMessage('Wanted to die');
        Response::json(['hi' => 'there']);
    }

}
class NotARenderer{}
