<?php

namespace Neoan\Model\Transformers;

use Neoan\Enums\Direction;
use Neoan\Model\Transformation;

class Hash implements Transformation
{

    private string $fakePassword = '*hashed*for*security*';

    public function __invoke(array $inputOutput, Direction $direction, string $property): array
    {
        if($direction === Direction::OUT && !empty($inputOutput[$property])){
            $inputOutput[$property] = $this->fakePassword;
        } elseif($direction === Direction::IN && isset($inputOutput[$property]) && $inputOutput[$property] !== $this->fakePassword) {
            $inputOutput[$property] = password_hash($inputOutput['password'], PASSWORD_DEFAULT);
        }
        return $inputOutput;
    }
}