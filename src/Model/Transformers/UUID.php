<?php

namespace Neoan\Model\Transformers;

use Neoan\Enums\Direction;
use Neoan\Model\Interfaces\Transformation;

class UUID implements Transformation
{

    public function __invoke(array $inputOutput, Direction $direction, string $property): array
    {
        if ($direction === Direction::IN && empty($inputOutput[$property])) {
            $inputOutput[$property] = \Ramsey\Uuid\Uuid::uuid4();
        }
        return $inputOutput;
    }
}