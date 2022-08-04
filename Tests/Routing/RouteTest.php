<?php

namespace Routing;

use Neoan\Routing\Routable;
use Neoan\Routing\Route;
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
        $ins = Route::get('/view', Routing::class);
        $ins->response(['handle','me']);
        $this->assertInstanceOf(Route::class, $ins);
    }
    public function testNotFound()
    {
        $r = new Route();
        $r();
    }
}

class Routing implements Routable{
    public function __invoke(array $provided): mixed
    {
        // TODO: Implement __invoke() method.
    }
}
