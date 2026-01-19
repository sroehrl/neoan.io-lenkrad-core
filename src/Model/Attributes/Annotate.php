<?php

namespace Neoan\Model\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class Annotate
{
    public function __construct(readonly string $annotation)
    {
    }

}