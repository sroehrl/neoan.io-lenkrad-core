<?php

namespace Neoan\Model\Traits;

use Neoan\Helper\DateHelper;
use Neoan\Model\Attributes\Initialize;
use Neoan\Model\Attributes\Transform;
use Neoan\Model\Attributes\Type;
use Neoan\Model\Transformers\CurrentTimeIn;

trait TimeStamps
{
    #[
        Type('datetime', null, 'CURRENT_TIMESTAMP'),
        Initialize(new DateHelper())
    ]
    public string $createdAt;
    #[Type('datetime')]
    #[Transform(CurrentTimeIn::class)]
    public ?string $updatedAt = null;
    #[Type('datetime')]
    public ?string $deletedAt = null;
}