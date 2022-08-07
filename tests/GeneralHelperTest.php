<?php

namespace Test;

use Neoan\Helper\Env;
use Neoan\Helper\VerifyJson;
use Neoan\Model\Collection;
use PHPUnit\Framework\TestCase;

class GeneralHelperTest extends TestCase
{
    function testEnv()
    {
        $res = Env::get('unset_env', 'default');
        $this->assertSame('default', $res);
    }
    function testVerifyJson()
    {
        $col = new VerifyJson(new Collection());
        $this->assertSame('"[]"', json_encode($col));

        // test serializable error
        $not = new VerifyJson(NAN);
        $this->assertStringContainsString("output-data", json_encode($not));
    }
}
