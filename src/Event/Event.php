<?php

namespace Neoan\Event;

use Neoan\Enums\GenericEvent;
use Neoan\Model\Model;
use Neoan\Routing\Routable;
use Neoan\Store\Store;

class Event
{
    private static ?string $runtimeIdentifier = null;
    private static array $registeredClosures;
    private static array $onAnyListeners = [];

    public static function onAny(callable $callable): void
    {
        self::$onAnyListeners[] = $callable;
    }

    public static function dispatch(string|GenericEvent $name, $event): void
    {
        self::init();
        $name = self::eventNameConversion($name);
        $marksman = debug_backtrace()[1];
        $from = $marksman['class'] . $marksman['type'] . $marksman['function'];
        // fire closures
        if (isset(self::$registeredClosures[$name])) {
            foreach (self::$registeredClosures[$name] as $closure) {
                $closure($event, $from, $marksman['args']);
            }
        }
        foreach (self::$onAnyListeners as $all) {
            $all($name, $event, $from, $marksman['args']);
        }
    }

    private static function init(): void
    {
        if (!self::$runtimeIdentifier) {
            self::$runtimeIdentifier = 'EventListeners' . rand(100, 999);
            Store::write(self::$runtimeIdentifier, []);
        }
    }

    private static function eventNameConversion(string|GenericEvent $name): string
    {
        return $name instanceof GenericEvent ? 'GENERIC:' . $name->name : $name;
    }

    public static function on(string|GenericEvent $eventName, callable $callable): void
    {
        $eventName = $name = self::eventNameConversion($eventName);
        self::$registeredClosures[$eventName][] = $callable;
    }

    public static function subscribeToClass(string $class, callable $closureOrInvokable): void
    {
        Store::write(self::$runtimeIdentifier, [
            ...Store::getInstance()->readValue(self::$runtimeIdentifier),
            ['class' => $class, 'callback' => $closureOrInvokable]
        ]);
    }

    public static function makeListenable(Routable|Model|Listenable $class): EventNotification
    {
        self::init();
        return new EventNotification(self::$runtimeIdentifier, $class);
    }
}