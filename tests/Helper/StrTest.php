<?php

namespace Test\Helper;

use Neoan\Helper\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{

    public function testToCamelCase()
    {
        $this->assertSame('testCase', Str::toCamelCase('test-case'));
    }

    public function testStartsWith()
    {
        $this->assertTrue(Str::startsWith('Some where over','Some'));
        $this->assertFalse(Str::startsWith('Some where over','Tom'));
    }

    public function testToSnakeCase()
    {
        $this->assertSame('test_case', Str::toSnakeCase('test-case'));
    }

    public function testEndsWith()
    {
        $this->assertTrue(Str::endsWith('Some where over','over'));
        $this->assertFalse(Str::endsWith('Some where over','for'));
    }

    public function testToKebabCase()
    {
        $this->assertSame('test-case', Str::toKebabCase('TestCase'));
    }

    public function testToUpperCase()
    {
        $this->assertSame('TESTCASE', Str::toUpperCase('TestCase'));
    }

    public function testContains()
    {
        $this->assertTrue(Str::contains('Some where over','where'));
        $this->assertFalse(Str::contains('Some where over','for'));
    }

    public function testToLowerCase()
    {
        $this->assertSame('testcase', Str::toLowerCase('TestCase'));
    }

    public function testRandomAlphaNumeric()
    {
        $this->assertSame(12, strlen(Str::randomAlphaNumeric(12)));
    }

    public function testToPascalCase()
    {
        $this->assertSame('TestCase', Str::toPascalCase('test_case'));
    }

    public function testToTitleCase()
    {
        $this->assertSame('Some Where Over', Str::toTitleCase('some where Over'));
    }

    public function testEncryption()
    {
        $key = Str::randomAlphaNumeric(12);
        $encrypted = Str::encrypt('Hello World', $key);
        $this->assertSame('Hello World', Str::decrypt($encrypted, $key));
    }
}
