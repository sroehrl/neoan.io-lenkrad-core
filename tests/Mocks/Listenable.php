<?php

namespace Test\Mocks;

use Neoan\Event\Event;
use Neoan\Event\EventNotification;
use Neoan\NeoanApp;

class Listenable implements \Neoan\Event\Listenable
{
    private EventNotification $notify;
    function __construct()
    {
        $this->notify = Event::makeListenable($this);
    }
    function __invoke(NeoanApp $app)
    {
        $app->testVariable = true;
    }
    function inform()
    {
        $this->notify->inform('test');
    }
}