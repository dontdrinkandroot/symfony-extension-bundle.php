<?php


namespace Net\Dontdrinkandroot\Symfony\ExtensionBundle\Tests\Utils;


use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Utils\StringUtils;

class StringUtilsTest extends \PHPUnit_Framework_TestCase
{

    public function testStartsWith()
    {
        $this->assertTrue(StringUtils::startsWith('bla', ''));
        $this->assertTrue(StringUtils::startsWith('bla', 'bl'));
        $this->assertfalse(StringUtils::startsWith('bla', 'la'));
    }

    public function testEndsWith()
    {
        $this->assertTrue(StringUtils::endsWith('bla', ''));
        $this->assertTrue(StringUtils::endsWith('bla', 'la'));
        $this->assertfalse(StringUtils::endsWith('bla', 'bl'));
    }

    public function testGetFirstChar()
    {
        $this->assertEquals('', StringUtils::getFirstChar(''));
        $this->assertEquals('b', StringUtils::getFirstChar('bla'));
    }

    public function testGetLastChar()
    {
        $this->assertEquals('', StringUtils::getLastChar(''));
        $this->assertEquals('a', StringUtils::getLastChar('bla'));
    }


}