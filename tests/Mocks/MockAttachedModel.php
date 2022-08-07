<?php

namespace Test\Mocks;

use Neoan\Model\Attributes\IsPrimaryKey;

class MockAttachedModel extends \Neoan\Model\Model
{
    #[IsPrimaryKey]
    public int $id;
    public int $mockId;
    public string $someValue = 'adam';

    const tableName = 'mockAttach';
}