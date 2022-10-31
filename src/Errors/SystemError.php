<?php

namespace Neoan\Errors;

use Neoan\Enums\GenericEvent;
use Neoan\Event\Event;
use Neoan\Helper\Terminate;
use Neoan\Response\Response;
use Neoan3\Apps\Template\Template;

class SystemError
{
    private static string $defaultSystemErrorTemplate = __DIR__ . '/defaultSystemError.html';

    public function __construct(string $message)
    {
        Event::dispatch(GenericEvent::UNRECOVERABLE_ERROR,[
            self::class => $message
        ]);
        $response = new Response();
        http_response_code(404);
        $response->respond(Template::embrace(file_get_contents(self::$defaultSystemErrorTemplate), [
            'msg' => $message,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 7)
        ]));
        Terminate::die();
    }
    public static function setTemplate(string $absolutePath): void
    {
        self::$defaultSystemErrorTemplate = $absolutePath;
    }
}