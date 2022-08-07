<?php

namespace Test\Event;

use Neoan\Event\Event;
use PHPUnit\Framework\TestCase;
use Test\Mocks\Listenable;

class EventTest extends TestCase
{

    public function testDispatch()
    {

        Event::on('generic-test-event',function($res){
            $this->assertSame('generic-event-content', $res);
        });
        Event::onAny(function($eventName, $content){
            if($content === 'generic-event-content'){
                $this->assertSame('generic-test-event', $eventName);
            }
        });
        Event::dispatch('generic-test-event', 'generic-event-content');
    }
    public function testClassSubscription()
    {
        $listenable = new Listenable();
        Event::subscribeToClass(Listenable::class, function($a, $b,$c,$event){
            $this->assertSame('test', $event);
        });
        $listenable->inform();
    }


}
