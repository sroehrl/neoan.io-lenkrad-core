<?php

namespace Neoan\Model\Attributes;

use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Model\ModelAttribute;

#[Attribute]
class IsUnique extends ModelAttribute
{
    public AttributeType $type = AttributeType::DECLARE;
    public function __construct(){}
    public function __invoke(){}
}