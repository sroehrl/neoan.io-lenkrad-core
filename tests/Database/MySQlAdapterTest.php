<?php

namespace Test\Database;

use Neoan\Database\Adapters\MySQLAdapter;
use PHPUnit\Framework\TestCase;
use Test\Mocks\MockPDO;

class MySQlAdapterTest extends TestCase
{
    function setUp(): void
    {
        $this->mock = new MockPDO();
    }


    public function testEasy()
    {
        $this->mock->setData([
            [
                ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => ''],
                ['Field' => 'eve', 'Type' => 'varchar(255)', 'Null' => 'YES', 'Key' => '', 'Default' => null, 'Extra' => '']
            ],
            [
                ['id' => 1, 'eve' => 'mother']
            ]
        ]);
        $adapter = new MySQLAdapter([], $this->mock);
        $result = $adapter->easy('adam.eve', ['id' => 1], ['orderBy' => ['adam.id', 'ASC'], 'limit' => [0, 1]]);
        $this->assertIsArray($result);
        $this->assertTrue($result[0]['eve'] === 'mother');
    }

    public function testRaw()
    {
        $this->mock->setData([
            [['id' =>2]]
        ]);
        $adapter = new MySQLAdapter([], $this->mock);
        $call = $adapter->raw('SELECT * FROM adam WHERE id = {{id}}', ['id' => 1]);
        $result = $call->fetchAll();
        $this->assertIsArray($result);
        $this->assertTrue($result[0]['id'] === 2);
    }

    public function testInsert()
    {
        $this->mock->setData([
            [
                ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => ''],
                ['Field' => 'eve', 'Type' => 'varchar(255)', 'Null' => 'YES', 'Key' => '', 'Default' => null, 'Extra' => '']
            ],
            [],
            ['id' => 5]
        ]);
        $adapter = new MySQLAdapter([], $this->mock);
        $result = $adapter->insert('adam', ['eve' => 'anything']);
        $this->assertSame(5, $result);
    }
    public function testUpdate()
    {
        $this->mock->setData([
            [
                ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => ''],
                ['Field' => 'eve', 'Type' => 'varchar(255)', 'Null' => 'YES', 'Key' => '', 'Default' => null, 'Extra' => ''],
                ['Field' => 'created', 'Type' => 'datetime', 'Null' => 'YES', 'Key' => '', 'Default' => null, 'Extra' => '']
            ],
            [[],[]]
        ]);
        $adapter = new MySQLAdapter([], $this->mock);
        $result = $adapter->update('adam', ['eve' => 'anything'], ['id' => 1, 'created' => '> 2024']);
        $this->assertSame(2, $result);
    }
    public function testHardDelete()
    {
        $this->mock->setData([
            [
                ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => ''],
                ['Field' => 'eve', 'Type' => 'varchar(255)', 'Null' => 'YES', 'Key' => '', 'Default' => null, 'Extra' => ''],
                ['Field' => 'created', 'Type' => 'datetime', 'Null' => 'YES', 'Key' => '', 'Default' => null, 'Extra' => '']
            ],
            [[]],
        ]);
        $adapter = new MySQLAdapter([], $this->mock);
        $int =$adapter->delete('adam', 1);
        $this->assertSame(1, $int);
    }
    public function testDateHandler()
    {
        $this->mock->setData([
            [
                ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => ''],
                ['Field' => 'eve', 'Type' => 'varchar(200)', 'Null' => 'NO', 'Key' => ''],
                ['Field' => 'created', 'Type' => 'datetime', 'Null' => 'YES', 'Key' => '', 'Default' => null, 'Extra' => ''],
                ['Field' => 'updated', 'Type' => 'datetime', 'Null' => 'YES', 'Key' => '', 'Default' => null, 'Extra' => ''],
                ['Field' => 'deleted', 'Type' => 'datetime', 'Null' => 'YES', 'Key' => '', 'Default' => null, 'Extra' => ''],
            ],
            [[]],
        ]);
        $adapter = new MySQLAdapter([], $this->mock);
        $result = $adapter->easy('adam.eve', [
            'id' => 1,
            [['created' => '2022-01-01'], ['created' => '<2022-01-01'], ['!created'],['^created']],
        ]);
        $this->assertIsArray($result);
    }
}
