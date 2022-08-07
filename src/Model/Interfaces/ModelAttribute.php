<?php

namespace Neoan\Model\Interfaces;

use Neoan\Enums\AttributeType;

interface ModelAttribute
{
    public function getType(): AttributeType;
}