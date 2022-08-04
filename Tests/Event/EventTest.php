<?php

namespace Test\Event;

use Neoan\Event\Event;
use PHPUnit\Framework\TestCase;

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


}
