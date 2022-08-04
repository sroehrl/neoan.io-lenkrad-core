<?php

namespace Neoan\Errors;

use Neoan\Helper\Terminate;
use Neoan\Response\Response;

class Unauthorized
{
    public function __construct()
    {
        $response = new Response();
        http_response_code(401);
        $response->respond('Unauthorized');
        Terminate::die();
    }
}