<?php

namespace Neoan\Errors;

use Neoan\Enums\GenericEvent;
use Neoan\Event\Event;
use Neoan\Helper\Terminate;
use Neoan\Response\Response;

class Unauthorized
{
    public function __construct()
    {
        Event::dispatch(GenericEvent::UNRECOVERABLE_ERROR,[
            self::class => '401: Unauthorized'
        ]);
        $response = new Response();
        http_response_code(401);
        $response->respond('Unauthorized');
        Terminate::die();
    }
}