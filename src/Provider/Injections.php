<?php

namespace Neoan\Provider;

class Injections
{
    private array $injections = [];
    public function __invoke(array $injections = []): Injections
    {
        $this->injections = [...$this->injections, ...$injections];
        return $this;
    }

    public function clear(): void
    {
        $this->injections = [];
    }

    public function set($key, $value): void
    {
        $this->injections[$key] = $value;
    }

    public function get(string $key, $default): mixed
    {
        return $this->injections[$key] ?? $default;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->injections;
    }
}