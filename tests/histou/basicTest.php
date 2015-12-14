<?php

namespace tests;

class BasicTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        spl_autoload_register('__autoload');
    }

    public function init()
    {
        $_GET['host'] = 'host';
        \histou\Basic::parsArgs();
    }

    public function testParseArgs()
    {
        $_GET['host'] = "host0";
        $_GET['service'] = "service0";
        $_GET['debug'] = "true";
        $_GET['height'] = "500px";
        $_GET['legend'] = "false";
        $_GET['annotations'] = "true";
        \histou\Basic::parsArgs();
        $this->assertEquals("host0", HOST);
        $this->assertEquals("service0", SERVICE);
        $this->assertEquals(true, \histou\Debug::isEnable());
        $this->assertEquals("500px", HEIGHT);
        $this->assertEquals(false, SHOW_LEGEND);
        $this->assertEquals(true, SHOW_ANNOTATION);
    }

    public function testParseArgsCommandline()
    {
		ob_start();
        \histou\Basic::parsArgs();
        $out1 = ob_get_contents();
        ob_end_clean();
        $this->assertEquals("<pre>Hostname is missing!<br>1<br>Hostname is missing!<br></pre>", $out1);

		ob_start();
        \histou\Basic::parsArgs();
        $out2 = ob_get_contents();
        ob_end_clean();
		$this->assertEquals("", $out2);

    }

    public function testParseIni()
    {
        \histou\Basic::parsIni('histou.ini.example');
        $this->assertEquals(PHP_COMMAND, "php");

        $this->assertEquals(\histou\Basic::parsIni('foo'), "Configuration not found");
    }

    public function testSetConstant()
    {
        $object = new \histou\Basic();
        $reflector = new \ReflectionClass('\histou\Basic');
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
        $reflector = new \ReflectionClass('\histou\Basic');
        $method = $reflector->getMethod('getConfigKey');
        $method->setAccessible(true);

        $result = $method->invokeArgs($object, array(array("foo" => array("bar" => "baz")), "1", "2"));
        $this->assertEquals($result, null);
    }

    public function testReturnData()
    {
        $this->init();
        \histou\Debug::enable();
        $dashboard = new \histou\grafana\Dashboard('foo');
        ob_start();
        \histou\Basic::returnData($dashboard);
        $out1 = ob_get_contents();
        ob_end_clean();
        $this->assertEquals($this->emptyDashboard, $out1);

        $_GET["callback"] = 1;
        ob_start();
        \histou\Basic::returnData('{"foo":"bar"}');
        $out2 = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('1({"foo":"bar"})', $out2);

        ob_start();
        \histou\Basic::returnData(1);
        $out3 = ob_get_contents();
        ob_end_clean();
        $this->assertEquals("<pre>Don't know what to do with this: 1</pre>", $out3);
    }
    private $emptyDashboard = '<pre>Array
(
    [id] => 1
    [title] => foo
    [originalTitle] => CustomDashboard
    [tags] => Array
        (
        )

    [timezone] => browser
    [editable] => 1
    [hideControls] => 1
    [sharedCrosshair] => 
    [nav] => Array
        (
            [0] => Array
                (
                    [type] => timepicker
                    [enable] => 1
                    [status] => Stable
                    [time_options] => Array
                        (
                            [0] => 5m
                            [1] => 15m
                            [2] => 1h
                            [3] => 6h
                            [4] => 12h
                            [5] => 24h
                            [6] => 2d
                            [7] => 7d
                            [8] => 30d
                        )

                    [refresh_intervals] => Array
                        (
                            [0] => 5s
                            [1] => 10s
                            [2] => 30s
                            [3] => 1m
                            [4] => 5m
                            [5] => 15m
                            [6] => 30m
                            [7] => 1h
                            [8] => 2h
                            [9] => 1d
                        )

                    [now] => 1
                    [collapse] => 
                    [notice] => 
                )

        )

    [time] => Array
        (
            [from] => now-8h
            [to] => now
        )

    [templating] => Array
        (
        )

    [annotations] => Array
        (
            [list] => Array
                (
                )

        )

    [refresh] => 30s
    [version] => 6
    [rows] => Array
        (
            [0] => Array
                (
                    [titel] => Debug
                    [editable] => 1
                    [height] => 400px
                    [panels] => Array
                        (
                            [0] => Array
                                (
                                    [title] => 
                                    [type] => text
                                    [span] => 12
                                    [editable] => 1
                                    [id] => 1
                                    [mode] => markdown
                                    [content] => 
                                )

                        )

                )

        )

)
<br>0<br>{"id":"1","title":"foo","originalTitle":"CustomDashboard","tags":[],"timezone":"browser","editable":true,"hideControls":true,"sharedCrosshair":false,"nav":[{"type":"timepicker","enable":true,"status":"Stable","time_options":["5m","15m","1h","6h","12h","24h","2d","7d","30d"],"refresh_intervals":["5s","10s","30s","1m","5m","15m","30m","1h","2h","1d"],"now":true,"collapse":false,"notice":false}],"time":{"from":"now-8h","to":"now"},"templating":[],"annotations":{"list":[]},"refresh":"30s","version":"6","rows":[{"titel":"Debug","editable":true,"height":"400px","panels":[{"title":"","type":"text","span":12,"editable":true,"id":1,"mode":"markdown","content":""}]}]}<br></pre>';
}
