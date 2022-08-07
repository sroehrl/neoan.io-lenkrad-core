<?php

namespace Neoan\Model\Interfaces;

use Neoan\Enums\Direction;

interface Transformation
{
    public function __invoke(array $inputOutput, Direction $direction, string $property): array;
}