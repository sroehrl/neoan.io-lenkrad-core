<?php

namespace Test\Provider;

use Neoan\Provider\Injections;
use PHPUnit\Framework\TestCase;

class InjectionsTest extends TestCase
{
    private Injections $injections;
    protected function setUp(): void
    {
        $this->injections = new Injections();
    }

    public function testClear()
    {
        $i = new Injections();
        $i(['some' => 'value']);
        $i->clear();
        $this->assertEmpty($i->toArray());
    }

    public function testGet()
    {
        $this->injections->set('a', 'b');
        $this->assertSame('b',  $this->injections->get('a', 'other'));
    }


}
