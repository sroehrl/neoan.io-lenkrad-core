<?php

namespace Test\Helper;

use Neoan\Database\SqLiteAdapter;
use Neoan\Enums\ResponseOutput;
use Neoan\Helper\Setup;
use PHPUnit\Framework\TestCase;

class SetupTest extends TestCase
{
    private Setup $setup;
    protected function setUp(): void
    {
        $this->setup = new Setup();
    }

    protected function tearDown(): void
    {
        $this->setup->setDefault404(dirname(__DIR__,2) . '/src/Errors/default404.html');
        $this->setup->setDefault500(dirname(__DIR__,2) . '/src/Errors/defaultSystemError.html');
    }

    public function testSetDefault404()
    {
        $this->setup->setDefault404('doesNotExist.html');
        $this->assertSame('doesNotExist.html', $this->setup->get('default404'));
    }

    public function testGetConfiguration()
    {
        $this->assertIsArray($this->setup->getConfiguration());
    }

    public function testSetSkeletonComponentPlacement()
    {
        $this->setup->setSkeletonComponentPlacement('haupt');
        $this->assertSame('haupt', $this->setup->get('skeletonComponentPlacement'));
    }

    public function testSetDatabaseAdapter()
    {
        $this->setup->setDatabaseAdapter(new SqLiteAdapter());
        $this->assertSame('Neoan\Database\SqLiteAdapter', $this->setup->get('databaseAdapter'));
    }

    public function testSet()
    {
        $this->setup->set('any', 'thing');
        $this->assertSame('thing', $this->setup->get('any'));
    }

    public function testSetSkeletonVariables()
    {
        $array = ['a', 'b', 'c'];
        $this->setup->setSkeletonVariables($array);
        $this->assertSame($array, $this->setup->get('skeletonVariables'));
    }

    public function testSetUseSkeleton()
    {
        $this->setup->setUseSkeleton(true);
        $this->assertTrue($this->setup->get('useSkeleton'));
    }

    public function testSetTemplatePath()
    {
        $this->setup->setTemplatePath('src/');
        $this->assertSame('src/', $this->setup->get('templatePath'));
    }

    public function testSetDefault500()
    {
        $this->setup->setDefault500('doesNotExist.html');
        $this->assertSame('doesNotExist.html', $this->setup->get('default500'));
    }

    public function testSetSkeletonHTML()
    {
        $this->setup->setSkeletonHTML('src/views/skeleton.html');
        $this->assertSame('src/views/skeleton.html', $this->setup->get('skeletonHTML'));
    }

    public function testDefaultOutput()
    {
        $this->setup->setDefaultOutput(ResponseOutput::HTML);
        $this->assertSame(ResponseOutput::HTML, $this->setup->get('defaultOutput'));
    }

    public function testInvoke()
    {
        $this->setup->setSkeletonComponentPlacement('haupt');
        $this->setup->setTemplatePath('src');
        $this->setup->setDefaultOutput(ResponseOutput::HTML);
        $this->setup->setUseSkeleton(true);
        $this->setup->setSkeletonHTML('some.html');
        $this->setup->setSkeletonVariables([]);
        $setup = $this->setup;
        $setup();
        $this->assertSame('haupt', $this->setup->get('skeletonComponentPlacement'));
    }

}
