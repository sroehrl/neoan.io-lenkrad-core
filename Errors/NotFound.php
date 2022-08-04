<?php

namespace Neoan\Errors;

use Neoan\Helper\Terminate;
use Neoan\Response\Response;
use Neoan3\Apps\Template;

class NotFound
{
    private static ?self $instance = null;
    private static string $default404Template = __DIR__ . '/default404.html';
    public function __construct($requestUri)
    {
        $response = new Response();
        http_response_code(404);
        $response->respond(Template::embrace(file_get_contents(self::$default404Template),['page'=>$requestUri]));
        Terminate::die();
    }
    public static function setTemplate(string $absolutePath): void
    {
        self::$default404Template = $absolutePath;
    }
}