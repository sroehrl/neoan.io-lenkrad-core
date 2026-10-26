<?php

namespace Neoan\Model\Traits;

use Neoan\Model\Attributes\Transform;
use Neoan\Model\Transformers\UUID;

trait HasUUID
{
    #[Transform(UUID::class)]
    public string $uuid;

    public static function byUUID(string $uuid): ?self
    {
        return self::retrieveOne(['uuid' => $uuid]);
    }


}