<?php

namespace Test\Mocks;

use Neoan\Model\Attributes\IsForeignKey;
use Neoan\Model\Attributes\IsPrimaryKey;

class MockAttachedModel extends \Neoan\Model\Model
{
    #[IsPrimaryKey]
    public int $id;
    #[IsForeignKey(MockModel::class)]
    public int $mockId;
    public string $someValue = 'adam';

    const tableName = 'mockAttach';
}