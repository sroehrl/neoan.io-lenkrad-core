<?php

namespace Neoan\Provider\Interfaces;

interface Provide extends \Iterator
{
    public function toArray(): array;
    public function get(string $which):mixed;
    public function set(string $which, mixed $provider):void;

}