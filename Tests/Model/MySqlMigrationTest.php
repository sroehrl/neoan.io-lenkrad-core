<?php

namespace Test\Model;

use Neoan\Database\Database;
use Neoan\Model\Migration\MySqlMigration;
use Test\Mocks\DatabaseTestAdapter;
use Test\Mocks\MockModel;
use PHPUnit\Framework\TestCase;

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
}
