<?php

namespace Test\Provider;

use Neoan\Provider\DefaultProvider;
use PHPUnit\Framework\TestCase;

class DefaultProviderTest extends TestCase
{
    function testIteration()
    {
        $provider = new DefaultProvider();
        $provider->set('a', []);
        $this->assertInstanceOf(\Iterator::class, $provider);
        $this->assertIsArray($provider->get('a'));
        $this->assertIsArray($provider->toArray());
        foreach ($provider as $key => $value){
            $this->assertIsArray($value);
        }
    }

}
