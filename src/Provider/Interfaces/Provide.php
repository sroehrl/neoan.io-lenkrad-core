<?php

namespace Neoan\Provider\Interfaces;

use Psr\Container\ContainerInterface;

interface Provide extends ContainerInterface
{
    public function toArray(): array;
    public function get(string $id):mixed;
    public function set(string $id, mixed $provider):void;

}