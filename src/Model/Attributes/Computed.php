<?php

namespace Neoan\Model\Attributes;

use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Model\Interfaces\ModelAttribute;
use Neoan\Model\Model;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class Computed implements ModelAttribute
{
    public mixed $initial;

    public function __construct(mixed $initial = null)
    {
        $this->initial = $initial;
    }

    function __invoke(Model &$currentModel, string $method)
    {
        $currentModel->{$method} = $currentModel->{$method}($this->initial);

    }

    public function getType(): AttributeType
    {
        return AttributeType::PRIVATE;
    }
}