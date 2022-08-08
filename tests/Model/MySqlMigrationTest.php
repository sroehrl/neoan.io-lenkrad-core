<?php

namespace Test\Model;

use Neoan\Database\Database;
use Neoan\Model\Migration\MySqlMigration;
use PHPUnit\Framework\TestCase;
use Test\Mocks\DatabaseTestAdapter;
use Test\Mocks\MockModel;

class MySqlMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        Database::connect(new DatabaseTestAdapter());
    }

    public function test__construct()
    {
        $migration = new MySqlMigration(MockModel::class);
        $this->assertIsString($migration->sql);
    }
    public function testAsSingleCommand()
    {
        $migration = new MySqlMigration(MockModel::class);
        $this->assertIsArray($migration->sqlAsSingleCommands());
    }
    public function testBackupOption()
    {

        $adapter = new DatabaseTestAdapter();
        $adapter->nextMockedResult = [[
            'Field' => 'id',
            'Key' => 'Primary Key'
        ]];
        Database::connect($adapter);
        $migration = new MySqlMigration(MockModel::class, 'copy_name');
        $this->assertStringStartsWith('CREATE', $migration->backupSql);
    }
    public function testFailBackupOption()
    {
        $this->expectException(\Exception::class);
        $migration = new MySqlMigration(MockModel::class, 'copy_name');
    }
}
