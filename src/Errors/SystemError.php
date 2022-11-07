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
        http_response_code(500);
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 10);
        $output = [];
        foreach ($backtrace as $i => $encounter){
            if($i === 0){
                // skip error class itself
                continue;
            }
            $output[$i] = [
                'class' => $encounter['class'],
                'method' => $encounter['function'],
                'line' => $encounter['line'],
                'arguments' => ''
            ];
            foreach ($encounter['args'] as $j => $argument) {
                if($argument instanceof \ReflectionParameter){
                    $argument = $argument->name;
                } elseif (is_object($argument)){
                    $argument = $argument::class;
                } elseif (is_array($argument)) {
                    $argument = json_encode($argument);
                }
                $output[$i]['arguments'] .= ($j >0? ', ':'') . $argument;
            }
        }
        $response->respond(Template::embrace(file_get_contents(self::$defaultSystemErrorTemplate), [
            'msg' => $message,
            'backtrace' => $output
        ]));
        Terminate::die();
    }
    public static function setTemplate(string $absolutePath): void
    {
        self::$defaultSystemErrorTemplate = $absolutePath;
    }
}