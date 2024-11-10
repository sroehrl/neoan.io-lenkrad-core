<?php

namespace Neoan\Helper;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

/**
 * @method void debug($message, array $context = [])
 * @method void info($message, array $context = [])
 * @method void notice($message, array $context = [])
 * @method void warning($message, array $context = [])
 * @method void error($message, array $context = [])
 * @method void critical($message, array $context = [])
 * @method void alert($message, array $context = [])
 * @method void emergency($message, array $context = [])
 * @method void log($level, $message, array $context = [])
 */
class Log
{
    private Logger $log;
    public function __construct()
    {
        $this->log = new Logger('lenkrad-app');
        $this->log->pushHandler(new ErrorLogHandler());
    }
    public function __invoke(): static
    {
        return $this;
    }

    public function __call(string $name, array $arguments): void
    {
        if(!method_exists($this->log, $name)) {
            $this->log->error('Log-method not found: '.$name);
            return;
        }
        $this->log->{$name}(...$arguments);
    }


    public function addHandler(HandlerInterface $handler): void
    {
        $this->log->pushHandler($handler);
    }
    public function setFileLocation(string $path): void
    {
        $this->addHandler(new StreamHandler($path, Level::Debug));
    }
    public function getMonologInstance(): Logger
    {
        return $this->log;
    }
}