<?php

namespace Test\Helper;

use Neoan\Helper\Setup;
use PHPUnit\Framework\TestCase;

class SetupTest extends TestCase
{
    private Setup $setup;
    protected function setUp(): void
    {
        $this->setup = new Setup();
    }

    public function testSetDefault404()
    {
        $this->setup->setDefault404();
    }

    public function testGetConfiguration()
    {

    }

    public function testGet()
    {

    }

    public function testSetSkeletonComponentPlacement()
    {

    }

    public function testSetDatabaseAdapter()
    {

    }

    public function testSet()
    {

    }

    public function testSetSkeletonVariables()
    {

    }

    public function testSetUseSkeleton()
    {

    }

    public function testSetTemplatePath()
    {

    }

    public function testSetDefault500()
    {

    }

    public function testSetSkeletonHTML()
    {

    }
}
