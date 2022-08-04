<?php

namespace Test\Database;

use Neoan\Database\Database;
use Test\Mocks\DatabaseTestAdapter;
use Test\Mocks\MockModel;
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
    function testDelete()
    {
        $insertId = Database::insert('mock',[
            'email'=>'same@'.time().'.de',
            'userName' => 'same'.time(),
            'password' => '123123'
        ]);

        // verify
        $this->assertNotEmpty(Database::easy('mock.id',['id'=>$insertId]));
        Database::delete('mock', $insertId);
        $this->assertEmpty(Database::easy('mock.id',['id'=>$insertId]));
    }
}
