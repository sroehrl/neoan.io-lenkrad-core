<?php

namespace Test\Provider;

use Neoan\Provider\DefaultProvider;
use Neoan\Provider\Interfaces\Provide;
use PHPUnit\Framework\TestCase;

class DefaultProviderTest extends TestCase
{
    function testToArray()
    {
        $provider = new DefaultProvider();
        $this->assertTrue($provider->has(DefaultProvider::class));
        $this->assertIsArray($provider->toArray());
    }
    function testTypeGuard()
    {
        $provider = new DefaultProvider();
        $this->expectErrorMessage('Wanted to die');
        $provider->get(WrongfullyRequiresInterface::class);
    }
    function testDefaultValue()
    {
        $provider = new DefaultProvider();
        $result = $provider->get(DefaultIsAvailable::class);
        $this->assertInstanceOf(DefaultIsAvailable::class, $result);
    }
    function testDefaultNotAvailable()
    {
        $provider = new DefaultProvider();
        $this->expectErrorMessage('Wanted to die');
        $provider->get(DefaultNotAvailable::class);
    }

}

class WrongfullyRequiresInterface{
    public function __invoke(Provide $provideInterface): self
    {
        return $this;
    }
}
class DefaultIsAvailable{
    public function __invoke(array $provided = []): self
    {
        return $this;
    }
}
class DefaultNotAvailable{
    public function __invoke(array $provided): self
    {
        return $this;
    }
}