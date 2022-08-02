<?php

namespace Neoan\Errors;

use Neoan\Response\Response;
use Neoan3\Apps\Template;

class Unauthorized
{
    public function __construct()
    {
        $response = new Response();
        http_response_code(401);
        $response->respond('Unauthorized');
        die();
    }
}