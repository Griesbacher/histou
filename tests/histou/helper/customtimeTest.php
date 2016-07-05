<?php

namespace tests\helper;

class CustomtimeTest extends \MyPHPUnitFrameworkTestCase
{
    public function testConvertToSeconds()
    {
        $this->assertSame(3, \histou\helper\CustomTime::convertToSeconds("3s"));
        $this->assertSame(180, \histou\helper\CustomTime::convertToSeconds("3m"));
        $this->assertSame(10800, \histou\helper\CustomTime::convertToSeconds("3h"));
        $this->assertSame(259200, \histou\helper\CustomTime::convertToSeconds("3d"));
        $this->assertSame(7776000, \histou\helper\CustomTime::convertToSeconds("3M"));
        $this->assertSame(-1, \histou\helper\CustomTime::convertToSeconds("3"));
        $this->assertSame(-2, \histou\helper\CustomTime::convertToSeconds("3Y"));
    }
    
    public function testGetLongestTime()
    {
        $this->assertSame('2s', \histou\helper\CustomTime::getLongestTime(array("1s", "2s")));
        $this->assertSame('60s', \histou\helper\CustomTime::getLongestTime(array("60s", "1m")));
        $this->assertSame('2m', \histou\helper\CustomTime::getLongestTime(array("100s", "2m")));
        $this->assertSame('0s', \histou\helper\CustomTime::getLongestTime(array()));
    }
}
