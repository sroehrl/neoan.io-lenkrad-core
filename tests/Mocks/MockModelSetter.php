<?php

namespace Test\Mocks;

use Neoan\Database\Database;
use Neoan\Model\Attributes\IsPrimaryKey;
use Neoan\Model\Model;
use Neoan\Model\Traits\Setter;
use Neoan\Model\Traits\TimeStamps;

class MockModelSetter extends Model
{
    #[IsPrimaryKey]
    public readonly int $id;

    public string $defaultString = 'Preset';

    use Setter;
    use TimeStamps;
    public function ensure()
    {
        $res = Database::raw('
            CREATE TABLE IF NOT EXISTS mock_model_setter(
                    id INTEGER PRIMARY KEY,
                    defaultString TEXT,
                    createdAt TIMESTAMP,
                    updatedAt DATETIME,
                    deletedAt DATETIME)
        ',[]);

    }
    public function dbReset()
    {
        Database::raw('DROP TABLE IF EXISTS mock_model_setter',[]);
    }
}