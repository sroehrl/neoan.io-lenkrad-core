<?php

namespace Database;

use Neoan\Database\Database;
use Neoan\Tests\Mocks\DatabaseTestAdapter;
use Neoan\Tests\Mocks\MockModel;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        Database::connect(new DatabaseTestAdapter());
        $mockModel = new MockModel();
        $mockModel->ensure();
    }

    function testRaw()
    {

        $res = Database::raw("SELECT date('now') as now;");
        $this->assertIsArray($res);
    }
    function testEasy()
    {
        $find = Database::easy('mock.id',['id'=>999999999]);
        $this->assertEmpty($find);
    }
}
