<?php

namespace Test\Routing;

use Neoan\Request\Request;
use Neoan\Response\Response;
use Neoan\Routing\Routable;
use Neoan\Routing\Route;
use Test\Mocks\MockRenderer;
use Test\Mocks\MockRequest;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{

    public function testMethods()
    {
        foreach(['get','put','post','delete','patch'] as $method){
            $ins = Route::$method('/path-'.$method, Routing::class);
            $this->assertInstanceOf(Route::class, $ins);
        }
    }
    public function testInject()
    {
        $ins = Route::get('/inject', Routing::class);
        $ins->inject(['a'=>'b']);
        $this->assertInstanceOf(Route::class, $ins);
    }
    public function testView()
    {
        $ins = Route::get('/view', Routing::class);
        $ins->view('some.html');
        $this->assertInstanceOf(Route::class, $ins);
    }
    public function testResponse()
    {
        $this->mockRequest('/some/name');
        $ins = Route::get('/some/:name', Routing::class);
        $ins->response([ResponseHandler::class,'output']);
        $this->assertInstanceOf(Route::class, $ins);
        $ins();
    }
    public function testNotFound()
    {
        $this->mockRequest('/some');

        $r = new Route();
        $this->expectException(\Exception::class);
        $r();

    }
    public function testRouteNotFound()
    {
        $this->mockRequest('/some');
        Route::get('/somewhere-else', Routing::class);
        $r = new Route();

        $this->expectException(\Exception::class);
        $r();
    }
    public function testNotRoutable()
    {
        $this->mockRequest('/try');
        Route::get('/try', NotRoutable::class);
        $r = new Route();

        $this->expectException(\Exception::class);
        $r();
    }
    public function testOnlyView()
    {
        $this->mockRequest('/some');
        Response::setDefaultRenderer(MockRenderer::class);
        Route::get('/some')->view('some.html');
        $r = new Route();
        $this->expectException(\Exception::class);
//        $this->expectErrorMessage('renderer');
        $r();

    }

    private function mockRequest($uri, $method = 'GET')
    {
        $mockRequest = new MockRequest();
        $mockRequest->requestMethod = $method;
        $mockRequest->requestUri = $uri;
        Request::getInstance($mockRequest);
    }

}

class Routing implements Routable{
    public function __invoke(array $provided): mixed
    {
        return ['result'=>'worked'];
    }
}
class NotRoutable{}

class ResponseHandler{
    static function output($result , $array): array
    {
        return [$result, $array];
    }
}
