<?php

namespace Test\Model;

use Neoan\Cli\MigrationHelper\ModelInterpreter;
use Neoan\Cli\MigrationHelper\MySqlMigration;
use Neoan\Database\Database;
use PHPUnit\Framework\TestCase;
use Test\Mocks\DatabaseTestAdapter;
use Test\Mocks\MockModel;

class MySqlMigrationTest extends TestCase
{
    private ModelInterpreter $modelInterpreter;
    protected function setUp(): void
    {
        Database::connect(new DatabaseTestAdapter());
        $this->modelInterpreter = new ModelInterpreter(MockModel::class);
    }

    public function test__construct()
    {

        $migration = new MySqlMigration($this->modelInterpreter);
        $this->assertIsString($migration->sql);
    }
    public function testAsSingleCommand()
    {
        $migration = new MySqlMigration($this->modelInterpreter);
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
        $migration = new MySqlMigration($this->modelInterpreter, 'copy_name');
        $this->assertStringStartsWith('CREATE', $migration->backupSql);
    }
    public function testFailBackupOption()
    {
        $this->expectException(\Exception::class);
        $migration = new MySqlMigration($this->modelInterpreter, 'copy_name');
    }
}
