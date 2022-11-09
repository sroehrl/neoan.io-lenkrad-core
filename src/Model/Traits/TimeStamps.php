<?php

namespace Neoan\Model\Traits;

use Neoan\Model\Attributes\Transform;
use Neoan\Model\Attributes\Type;
use Neoan\Model\Helper\DateTimeProperty;
use Neoan\Model\Transformers\CurrentTimeIn;
use Neoan\Model\Transformers\LockedTimeIn;

trait TimeStamps
{
    #[
        Type('datetime', null, 'CURRENT_TIMESTAMP'),
        Transform(LockedTimeIn::class)
    ]
    public ?DateTimeProperty $createdAt = null;

    #[
        Type('datetime'),
        Transform(CurrentTimeIn::class)
    ]
    public ?DateTimeProperty $updatedAt;

    #[
        Type('datetime'),
        Transform(LockedTimeIn::class)
    ]
    public ?DateTimeProperty $deletedAt = null;

}