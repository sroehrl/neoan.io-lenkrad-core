<?php

namespace Test\Model;

use Neoan\Database\Database;
use Neoan\Enums\TransactionType;
use Neoan\Model\Attributes\Initialize;
use Neoan\Model\Collection;
use Neoan\Model\Interpreter;
use PHPUnit\Framework\TestCase;
use Test\Mocks\DatabaseTestAdapter;
use Test\Mocks\MockModel;

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
        // one
        $one = MockModel::retrieveOne(['id' => 1]);
        $this->assertInstanceOf(MockModel::class, $one);
        // retrieveOne is null
        $this->assertNull(MockModel::retrieveOne(['id' => 9999999]));
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

}
class Fake{
    #[Initialize('bubu')]
    public string $dont = 'Default value';
}