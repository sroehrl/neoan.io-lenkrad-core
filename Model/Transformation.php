<?php

namespace Neoan\Model;

use Neoan\Enums\Direction;

interface Transformation
{
    public function __invoke(array $inputOutput, Direction $direction, string $property): array;
}