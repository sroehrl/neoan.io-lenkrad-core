<?php

namespace Test\Routing;

use Neoan\Enums\GenericEvent;
use Neoan\Enums\RequestMethod;
use Neoan\Event\Event;
use Neoan\NeoanApp;
use Neoan\Routing\AttributeRouting;
use Neoan\Routing\Attributes\Get;
use PHPUnit\Framework\TestCase;
use Test\Mocks\MockController;

class AttributeRoutingTest extends TestCase
{
    function testAutoRoute()
    {
        $ar = new AttributeRouting('Test');
        $ar(new NeoanApp(dirname(__DIR__),__DIR__, dirname(__DIR__,2)));
        $this->assertSame(1, $this->count($ar->routingClasses));
    }
    function testRoutingAttributes()
    {
        $attributes = ['Get','Patch','Post','Put','Delete'];
        foreach($attributes as $attribute){
            $ns = "Neoan\Routing\Attributes\\$attribute";
            $route = new $ns('/really/strange/route');
            $route->setControllerClass(MockController::class);
            $allUpper = array_map(fn($attr) => strtoupper($attr), $attributes);
            Event::on(GenericEvent::ROUTE_REGISTERED, function($event) use($allUpper){
                if($event['route'] === '/really/strange/route'){
                    $this->assertTrue(in_array($event['method']->name, $allUpper));
                }

            });
            $route->generateRoute();
        }


    }
}
