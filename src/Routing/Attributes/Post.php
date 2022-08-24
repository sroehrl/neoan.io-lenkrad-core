<?php

namespace Neoan\Routing\Attributes;

use Attribute;
use Neoan\Enums\RequestMethod;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Post extends Route
{

    public function __construct(string $route, ...$middleware)
    {
        parent::__construct(RequestMethod::POST, $route, ...$middleware);
    }


}