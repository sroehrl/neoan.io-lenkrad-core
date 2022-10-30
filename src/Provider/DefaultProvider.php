<?php

namespace Neoan\Provider;

use Neoan\Provider\Interfaces\Provide;

class DefaultProvider implements Provide
{

    private array $providers = [];

    public function toArray(): array
    {
        return $this->providers;
    }

    public function get(string $which): mixed
    {
        return $this->providers[$which];
    }

    public function set(string $which, mixed $provider): void
    {
        $this->providers[$which] = $provider;
    }

    public function current(): mixed
    {
        return current($this->providers);
    }

    public function next(): void
    {
        next($this->providers);
    }

    public function key(): string|int|null
    {
        return key($this->providers);
    }

    public function valid(): bool
    {
        return array_key_exists($this->key(), $this->providers);
    }

    public function rewind(): void
    {
        reset($this->providers);
    }
}