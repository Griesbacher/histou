<?php

require_once 'histou/basic.php';

class StackTest extends PHPUnit_Framework_TestCase
{
    public function testSetConstant()
    {
		$object = new  \histou\Basic();
		$reflector = new ReflectionClass( '\histou\Basic' );
		$method = $reflector->getMethod( 'setConstant' );
		$method->setAccessible( true );

		$method->invokeArgs( $object, array("KEY", "value", ""));
        $this->assertEquals(KEY, "value");

		$method->invokeArgs( $object, array("KEY2", "", "alt"));
        $this->assertEquals(KEY2, "alt");
    }
}
