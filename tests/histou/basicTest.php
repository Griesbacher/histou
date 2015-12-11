<?php

require_once 'histou/basic.php';

class BasicTest extends PHPUnit_Framework_TestCase
{
    public function testParseIni()
    {
        \histou\Basic::parsIni('histou.ini.example');
        $this->assertEquals(PHP_COMMAND, "php");

        $this->assertEquals(\histou\Basic::parsIni('foo'), "Configuration not found");
    }

    public function testSetConstant()
    {
        $object = new \histou\Basic();
        $reflector = new ReflectionClass('\histou\Basic');
        $method = $reflector->getMethod('setConstant');
        $method->setAccessible(true);

        $method->invokeArgs($object, array("KEY", "value", ""));
        $this->assertEquals(KEY, "value");

        $method->invokeArgs($object, array("KEY2", "", "alt"));
        $this->assertEquals(KEY2, "alt");
    }

    public function testGetConfigKey()
    {
        $object = new \histou\Basic();
        $reflector = new ReflectionClass('\histou\Basic');
        $method = $reflector->getMethod('getConfigKey');
        $method->setAccessible(true);

        $result = $method->invokeArgs($object, array(array("foo" => array("bar" => "baz")), "1", "2"));
        $this->assertEquals($result, null);
    }
}
