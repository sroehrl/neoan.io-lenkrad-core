<?php

namespace Neoan\Model\Traits;

trait Setter
{
    public function set(string $propertyName, mixed $value): static
    {
        if (property_exists($this, $propertyName)) {
            $this->{$propertyName} = $value;
        }
        return $this;
    }
}