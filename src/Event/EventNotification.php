<?php

namespace Neoan\Event;

use Neoan\Store\Store;

class EventNotification
{
    private static string $identifier;
    private static mixed $class;

    function __construct($identifier, $class)
    {
        self::$identifier = $identifier;
        self::$class = $class;
    }

    function inform(mixed $anything = null): void
    {
        $marksman = debug_backtrace()[1];
        $eventName = self::$class::class . $marksman['type'] . $marksman['function'];
        Event::dispatch($eventName, $eventName);
        foreach (Store::dynamic(self::$identifier)->get() as $dynamic) {
            if ($dynamic['class'] === self::$class::class) {
                $dynamic['callback']($eventName, self::$class, $marksman['args'], $anything);
            }
        }
    }

}