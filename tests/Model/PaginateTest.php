<?php

namespace Test\Model;

use Neoan\Database\Database;
use Neoan\Model\Collection;
use Neoan\Model\Paginate;
use PHPUnit\Framework\TestCase;
use Test\Mocks\DatabaseTestAdapter;
use Test\Mocks\MockModel;

class PaginateTest extends TestCase
{
    protected function setUp(): void
    {
        Database::connect(new DatabaseTestAdapter());
        $randomUser = 'heinz-' . microtime(true);
        $model = new MockModel([
            'userName' => $randomUser,
            'email' => 'heinz@sam.de',
            'password' => '123123'
        ]);
        $model->store();

    }
    protected function tearDown(): void
    {
        Database::raw('DELETE FROM `mock`');
    }
    public function testPage()
    {
        $pagination = new Paginate(1,20, MockModel::class);
        $test = $pagination->where(['id'=>1])
            ->descending('id')
            ->ascending('id')
            ->get();
        $this->assertSame(1, $pagination->getPage());
        $this->assertSame(20, $pagination->getPageSize());
        $this->assertInstanceOf(Collection::class, $test['collection']);
    }
}
