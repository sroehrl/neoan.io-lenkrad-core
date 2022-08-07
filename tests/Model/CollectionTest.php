<?php

namespace Test\Model;

use Neoan\Database\Database;
use Neoan\Model\Collection;
use PHPUnit\Framework\TestCase;
use Test\Mocks\DatabaseTestAdapter;
use Test\Mocks\MockModel;

class CollectionTest extends TestCase
{
    private MockModel $model;
    private Collection $collection;
    private ?MockModel $lastItem = null;
    protected function setUp(): void
    {
        Database::connect(new DatabaseTestAdapter());
        $randomUser = 'heinz-' . time();
        $this->model = new MockModel([
            'userName' => $randomUser,
            'email' => 'heinz@sam.de',
            'password' => '123123'
        ]);
        $this->model->ensure()->store();
        $this->collection = MockModel::retrieve(['userName'=>$randomUser]);

    }
    protected function tearDown(): void
    {
        Database::raw('DELETE FROM `mock`');
    }

    public function testEach()
    {
        $this->collection->each(function ($item, $iterator){
            $this->lastItem = $item;
        });
        $this->assertInstanceOf(MockModel::class, $this->lastItem);
    }

    public function testAdd()
    {
        $this->collection->add(new MockModel());
        $this->assertSame(2, $this->collection->count());
    }

    public function testToArray()
    {
        $this->collection->add(new MockModel());
        $this->assertIsArray($this->collection->toArray());
        foreach($this->collection as $item){
            $this->assertInstanceOf(MockModel::class, $item);
        }
    }

    public function testStore()
    {
        $this->collection->add(new MockModel([
            'userName' => 'different',
            'email'=>'as@as.de',
            'password'=>'safe'
        ]));
        $this->assertInstanceOf(Collection::class, $this->collection->store());
    }
}
