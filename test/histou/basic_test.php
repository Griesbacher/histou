<?php

require_once 'histou/basic.php';

class StackTest extends PHPUnit_Framework_TestCase
{
    public function testSetConstant()
    {
        setConstant("KEY", "value", "");
        $this->assertEquals(KEY, "value");
        setConstant("KEY2", "", "alt");
        $this->assertEquals(KEY2, "alt");
    }
}
?>
