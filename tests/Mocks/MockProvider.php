<?php

namespace Test\Mocks;

use Neoan\Provider\Interfaces\Provide;

class MockProvider implements Provide
{

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        // TODO: Implement has() method.
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }

    public function get(string $id): mixed
    {
        // TODO: Implement get() method.
    }

    public function set(string $id, mixed $provider): void
    {
        // TODO: Implement set() method.
    }
}