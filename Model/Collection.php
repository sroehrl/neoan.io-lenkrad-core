<?php

namespace Neoan\Model;

use Iterator;

class Collection implements Iterator
{
    private int $position;

    private array $modelInstances = [];

    public function __construct() {
        $this->position = 0;
    }

    function each(callable $callback): self
    {
        foreach ($this->modelInstances as $i => $modelInstance){
            $callback($modelInstance, $i);
        }
        return $this;
    }

    function add(Model $modelInstance): self
    {
        $this->modelInstances[] = $modelInstance;
        return $this;
    }
    function toArray(): array
    {
        $output = [];
        foreach ($this->modelInstances as $modelInstance){
            $output[] = $modelInstance->toArray();
        }
        return $output;
    }
    function store(): self
    {
        foreach ($this->modelInstances as $modelInstance){
            $modelInstance->store();
        }
        return $this;
    }
    function count(): int
    {
        return count($this->modelInstances);
    }

    public function current(): mixed
    {
        return $this->modelInstances[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->modelInstances[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}