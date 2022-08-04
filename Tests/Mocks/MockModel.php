<?php

namespace Neoan\Tests\Mocks;

use Neoan\Database\Database;
use Neoan\Model\Attributes\HasMany;
use Neoan\Model\Attributes\IsPrimaryKey;
use Neoan\Model\Attributes\IsUnique;
use Neoan\Model\Attributes\Transform;
use Neoan\Model\Attributes\Type;
use Neoan\Model\Collection;
use Neoan\Model\Model;
use Neoan\Model\Transformers\Hash;

class MockModel extends Model
{
    const tableName = 'mock';

    #[IsPrimaryKey]
    public int $id;
    #[IsUnique]
    public string $userName;
    #[Transform(Hash::class)]
    public string $password;
    #[Type('TEXT')]
    public string $email;

    #[HasMany(MockAttachedModel::class,['mockId'=>'id'])]
    public Collection $attached;

    function ensure(): self
    {
        Database::raw('
        CREATE TABLE IF NOT EXISTS mock(
                    id INTEGER PRIMARY KEY, 
                    email TEXT,
                    userName TEXT UNIQUE ,
                    password TEXT)
        ');
        Database::raw('
        CREATE TABLE IF NOT EXISTS mockAttach(
                    id INTEGER PRIMARY KEY, 
                    mockId INTEGER,
                    someValue TEXT)
        ');
        return $this;
    }
}