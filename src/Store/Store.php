<?php

namespace Neoan\Store;

class Store
{
    private static ?self $instance = null;
    private array $storage = [];

    public static function getInstance(): ?Store
    {
        if(self::$instance == null){
            self::$instance = new Store();
        }
        return self::$instance;
    }
    public static function write(string $variable, mixed $value): void
    {
        $instance = self::getInstance();
        $instance->storage[$variable] = $value;
    }
    public static function dynamic(string $variableName): Dynamic
    {
        $instance = self::getInstance();
        return new Dynamic($instance, $variableName);
    }
    public function readValue(string $variable)
    {
        $instance = self::getInstance();
        return $instance->storage[$variable] ?? null;
    }

}
class Dynamic
{
    private Store $instance;
    private string $watchedVariable;
    public function __construct($instance, $variableName)
    {
        $this->instance = $instance;
        $this->watchedVariable = $variableName;
    }
    public function __toString(): string
    {
        return $this->instance->readValue($this->watchedVariable);
    }
    public function get()
    {
        return $this->instance->readValue($this->watchedVariable);
    }
}