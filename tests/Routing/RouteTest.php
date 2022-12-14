<?php

namespace Test\Routing;

use Neoan\Helper\Setup;
use Neoan\NeoanApp;
use Neoan\Provider\DefaultProvider;
use Neoan\Request\Request;
use Neoan\Response\Response;
use Neoan\Routing\Interfaces\Routable;
use Neoan\Routing\Route;
use PHPUnit\Framework\TestCase;
use Test\Mocks\MockRenderer;
use Test\Mocks\MockRequest;

class RouteTest extends TestCase
{
    private NeoanApp $app;
    public function setUp(): void
    {
        $setup = new Setup();
        $setup->setLibraryPath(__DIR__)->setPublicPath(__DIR__);
        $this->app = new NeoanApp($setup, dirname(__DIR__));
    }

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
        $ins($this->app);
    }
    public function testNotFound()
    {
        $this->mockRequest('/some');
        $this->setOutputCallback(function($output){
            var_dump($output);
        });
        $r = new Route();
        $this->expectException(\Exception::class);
        $r($this->app);

    }
    public function testRouteNotFound()
    {
        $this->mockRequest('/some');
        $this->setOutputCallback(function($output){
            var_dump($output);
        });
        Route::get('/somewhere-else', Routing::class);
        $r = new Route();

        $this->expectException(\Exception::class);
        $r($this->app);
    }
    public function testNotRoutable()
    {
        $this->mockRequest('/try');
        Route::get('/try', NotRoutable::class);
        $r = new Route();

        $this->expectException(\Exception::class);
        $r($this->app);
    }
    public function testOnlyView()
    {
        $this->mockRequest('/some');
        Response::setDefaultRenderer(MockRenderer::class);
        Route::get('/some')->view('some.html');
        $r = new Route();
        $this->expectException(\Exception::class);
//        $this->expectErrorMessage('renderer');
        $r($this->app);

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
    public function __invoke(DefaultProvider $provided): mixed
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
