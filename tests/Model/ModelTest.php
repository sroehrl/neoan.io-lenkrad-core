<?php

namespace Test\Model;

use Neoan\Database\Database;
use Neoan\Enums\TransactionType;
use Neoan\Model\Attributes\Initialize;
use Neoan\Model\Collection;
use Neoan\Model\Interpreter;
use Neoan\Model\Model;
use PHPUnit\Framework\TestCase;
use Test\Mocks\DatabaseTestAdapter;
use Test\Mocks\MockAttachedModel;
use Test\Mocks\MockModel;
use Test\Mocks\MockModelSetter;

class ModelTest extends TestCase
{
    private MockModel $model;

    protected function setUp(): void
    {
        Database::connect(new DatabaseTestAdapter());
        $randomUser = 'heinz-' . microtime(true);
        $this->model = new MockModel([
            'userName' => $randomUser,
            'email' => 'heinz@sam.de',
            'password' => '123123'
        ]);
        $this->model->store();

    }
    protected function tearDown(): void
    {
        Database::raw('DELETE FROM `mock`');
    }

    public function testConstructor()
    {
        $this->assertStringStartsWith('heinz-', $this->model->userName);
    }

    public function testDeclaration()
    {
        $declaration = MockModel::declare();
        $this->assertSame('id', $declaration['mock'][0]['name']);
    }

    public function testTransaction()
    {
        $randomUserName = 'test-' . time();
        $this->model->email = 'some@email';
        $this->model->userName = $randomUserName;
        $this->model->password = '123123';
        $this->model->store();
        // has id?
        $this->assertObjectHasAttribute('id', $this->model);
        $this->assertIsInt($this->model->id);
        $this->model->email = 'changed@mail.com';
        $this->model->store();
        $this->assertSame('changed@mail.com', $this->model->email);
    }

    public function testRetrieval()
    {
        // many
        $collection = MockModel::retrieve(['id' => 1]);
        $this->assertInstanceOf(Collection::class, $collection);
        foreach ($collection as $key => $model) {
            $this->assertInstanceOf(Model::class, $model);
        }
        // one
        $one = MockModel::retrieveOne(['id' => 1]);
        $this->assertInstanceOf(MockModel::class, $one);
        // retrieveOne is null
        $this->assertNull(MockModel::retrieveOne(['id' => 9999999]));
    }
    public function testDeleteHard()
    {

        $this->model->delete(true);
        $this->assertEmpty(Database::raw('SELECT id FROM mockAttach WHERE id = {{id}}', [
            'id' => $this->model->id
        ]));
    }
    public function testDeleteSoft()
    {
        $se = new MockModelSetter(['defaultString' => 'some']);
        $se->dbReset();
        $se->ensure();
        $se->store();
        $newSe = MockModelSetter::get(1);
        $this->assertSame(1, $newSe->id);
        // soft delete
        $newSe->delete();
        $test = MockModelSetter::get(1);
        $this->assertNull($test->deletedAt);
    }

    public function testGetTransactionMode()
    {
        $this->assertInstanceOf(TransactionType::class, $this->model->getTransactionMode());
    }
    public function testGetFail()
    {
        $this->expectException(\Exception::class);
        MockModel::get(99999);
    }
    public function testInterpreter()
    {
        $interpreter = new Interpreter(Fake::class);
        $this->assertSame('fake', $interpreter->getTableName());
    }
    public function testReadOnlySetter()
    {
        $nm = new MockModelSetter(['id'=>5]);
        $this->assertSame(5, $nm->id);
    }
    public function testTypeError()
    {
        $nm = new MockModel(['id'=>'string']);
        $this->assertTrue(!isset($nm->id));
    }
    public function testSelectorString()
    {
        $interpreter = new Interpreter(MockModelSetter::class);
        $res = $interpreter->generateSelect();
        $this->assertStringContainsString('defaultString', $res['selectorString']);
    }
    public function testRetrieveOneORCreate()
    {
        $nm = MockModel::retrieveOneOrCreate(['userName' => 'notGiven']);
        $this->assertFalse(isset($nm->id));
        // then store
        $nm->store();
        $nm2 = MockModel::retrieveOneOrCreate(['userName' => 'notGiven']);
        $this->assertTrue(isset($nm2->id));
    }
    public function testMagicFail()
    {
        $nm = new MockModel();
        $this->expectException(\Exception::class);
        $nm->users();
    }
    public function testMagic()
    {
        $ma = new MockAttachedModel();
        $ma->mockId = 1;
        $this->assertInstanceOf(MockModel::class,$ma->mock());
    }
    public function testPagination()
    {
        $this->expectException(\PDOException::class);
        MockModel::paginate()->get();
    }

}
class Fake{
    #[Initialize('bubu')]
    public string $dont = 'Default value';
}