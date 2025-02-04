<?php

namespace Test\Database;

use Neoan\Database\Adapters\SqLiteAdapter;
use Neoan\Database\Database;
use PHPUnit\Framework\TestCase;

class SqLiteAdapterTest extends TestCase
{
    function setUp(): void
    {
        Database::connect(new SqLiteAdapter(['location' => __DIR__ . '/db.db']));
        Database::raw('CREATE TABLE IF NOT EXISTS test_me(
                    id INTEGER PRIMARY KEY,
                    some TEXT,
                    deletedAt DATETIME,
                    one TEXT UNIQUE)');
    }
    public static function tearDownAfterClass(): void
    {
        Database::connect(new SqLiteAdapter(['location' => __DIR__ . '/db.db']));
        Database::raw('DROP TABLE test_me',[]);
    }
    function testMethods()
    {
        // insert & easy
        Database::insert('test_me', ['some' => 'etwas']);
        $res = Database::easy('test_me.some',['id'=>1]);
        $this->assertIsArray($res);
        $this->assertSame('etwas', $res[0]['some']);

        // update
        $update = Database::update('test_me', ['one' => 'two','some'=>null],['some' => 'etwas']);
        $this->assertSame('two', Database::easy('test_me.one')[0]['one']);

        // raw
        $raw = Database::raw('SELECT * FROM test_me WHERE id = {{id}}',['id'=>1]);
        $this->assertSame('two', $raw[0]['one']);

        // delete
        Database::delete('test_me',1);
        $soft = Database::easy('test_me.deletedAt', ['id'=>1]);
        $this->assertNotEmpty($soft);
        $this->assertNotEmpty($soft[0]['deletedAt']);
        $hard = Database::delete('test_me',1, true);
        $this->assertEmpty($hard);

    }
    function testCallFunctions()
    {
        // clear db
        Database::raw('DELETE FROM test_me');
        // insert 3 rows
        foreach(['one','two','three'] as $some){
            Database::insert('test_me', ['some' => $some]);
        }
        // test limit
        $call = Database::easy('test_me.*',[],['limit'=> [0,1]]);
        $this->assertSame(1, count($call));
        // test orderBy
        $call = Database::easy('test_me.*',[],['orderBy'=> ['id','desc']]);
        $this->assertTrue($call[0]['id'] > $call[1]['id']);
    }
}
