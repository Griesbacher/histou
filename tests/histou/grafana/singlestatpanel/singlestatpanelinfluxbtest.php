<?php

namespace tests\grafana;

class SinglestatPanelInfluxdbTest extends \MyPHPUnitFrameworkTestCase
{
    public function init()
    {
        $_GET['host'] = 'host';
        \histou\Basic::parsIni('histou.ini.example');
        \histou\Basic::parsArgs();
    }

    public function testCreateSinglestatPanelInfluxdb()
    {
        $this->init();
        $spanel = \histou\grafana\singlestatpanel\SinglestatPanelFactory::generatePanel('spanel');
        $this->assertSame(array (
                            'title' => 'spanel',
                            'type' => 'singlestat',
                            'span' => 12,
                            'editable' => true,
                            'id' => 1,
                            'targets' =>    array (    ),
                            'rangeMaps' =>    array (    ),
                            'valueMaps' =>    array (    ),  ), $spanel->toArray());
    }
    
    public function testAddTargetSinglestatPanelInfluxdb()
    {
        $this->init();
        $spanel = \histou\grafana\singlestatpanel\SinglestatPanelFactory::generatePanel('spanel');
        $spanel->addTarget(array("123"));
        $this->assertSame(array (array("123")), $spanel->toArray()['targets']);
    }
    public function testSetColorPanelInfluxdb()
    {
        $this->init();
        $spanel = \histou\grafana\singlestatpanel\SinglestatPanelFactory::generatePanel('spanel');
        $spanel->setColor(array("#123"), false, true);
        $this->assertSame(array("#123"), $spanel->toArray()['colors']);
        $this->assertSame(false, $spanel->toArray()['colorBackground']);
        $this->assertSame(true, $spanel->toArray()['colorValue']);
    }
    public function testSetThresholdsPanelInfluxdb()
    {
        $this->init();
        $spanel = \histou\grafana\singlestatpanel\SinglestatPanelFactory::generatePanel('spanel');
        $spanel->setThresholds(5);
        $this->assertSame(5, $spanel->toArray()['thresholds']);
        $spanel->setThresholds(5, 10);
        $this->assertSame("5,10", $spanel->toArray()['thresholds']);
    }
    public function testaddRangeToTextElementPanelInfluxdb()
    {
        $this->init();
        $spanel = \histou\grafana\singlestatpanel\SinglestatPanelFactory::generatePanel('spanel');
        $spanel->addRangeToTextElement(1, 3, 'foo');
        $this->assertSame(2, $spanel->toArray()['mappingType']);
        $this->assertSame(array(array('from' => 1, 'to' => 3, 'text' => 'foo')), $spanel->toArray()['rangeMaps']);
        $spanel->addRangeToTextElement(4, 5, 'baz');
        $this->assertSame(array(
                                array('from' => 1, 'to' => 3, 'text' => 'foo'),
                                array('from' => 4, 'to' => 5, 'text' => 'baz')), $spanel->toArray()['rangeMaps']);
    }
    public function testAddValueToTextElementPanelInfluxdb()
    {
        $this->init();
        $spanel = \histou\grafana\singlestatpanel\SinglestatPanelFactory::generatePanel('spanel');
        $spanel->addValueToTextElement(1, 'foo');
        $this->assertSame(1, $spanel->toArray()['mappingType']);
        $this->assertSame(array(array('op' => '=', 'value' => 1, 'text' => 'foo')), $spanel->toArray()['valueMaps']);
        $spanel->addValueToTextElement(3, 'baz');
        $this->assertSame(array(
                                array('op' => '=', 'value' => 1, 'text' => 'foo'),
                                array('op' => '=', 'value' => 3, 'text' => 'baz')), $spanel->toArray()['valueMaps']);
    }
    public function testGenTargetSimplePanelInfluxdb()
    {
        $this->init();
        $spanel = \histou\grafana\singlestatpanel\SinglestatPanelFactory::generatePanel('spanel');
        $target = $spanel->genTargetSimple("host", "service", "command", "perflabel");
        $expected = array (
        'measurement' => 'metrics',
        'alias' => '$col',
        'select' =>
        array (
        array (
        array (
        'type' => 'field',
        'params' => array ('value'),
        ),
        array (
        'type' => 'last',
        'params' => array (),
        ),
        ),
        ),
        'tags' =>
        array (
        array (
        'key' => 'host',
        'operator' => '=',
        'value' => 'host',
        ),
        array (
        'condition' => 'AND',
        'key' => 'service',
        'operator' => '=',
        'value' => 'service',
        ),
        array (
        'condition' => 'AND',
        'key' => 'command',
        'operator' => '=',
        'value' => 'command',
        ),
        array (
        'condition' => 'AND',
        'key' => 'performanceLabel',
        'operator' => '=',
        'value' => 'perflabel',
        ),
        ),
        'dsType' => 'influxdb',
        'resultFormat' => 'time_series',
        'datasource' => 'nagflux',
        'groupBy' =>
        array (),
        );
        $this->assertSame($expected, $target);
        $rtarget = $spanel->genTargetSimple("host", "service", "command", "perflabel", true);
        $rexpected = array (
        'measurement' => 'metrics',
        'alias' => '$col',
        'select' =>
        array (
        array (
        array (
        'type' => 'field',
        'params' =>
        array ('value'),
        ),
        array (
        'type' => 'last',
        'params' => array (),
        ),
        ),
        ),
        'tags' =>
        array (
        array (
        'key' => 'host',
        'operator' => '=~',
        'value' => '/^host$/',
        ),
        array (
        'condition' => 'AND',
        'key' => 'service',
        'operator' => '=~',
        'value' => '/^service$/',
        ),
        array (
        'condition' => 'AND',
        'key' => 'command',
        'operator' => '=~',
        'value' => '/^command$/',
        ),
        array (
        'condition' => 'AND',
        'key' => 'performanceLabel',
        'operator' => '=~',
        'value' => '/^perflabel$/',
        ),
        ),
        'dsType' => 'influxdb',
        'resultFormat' => 'time_series',
        'datasource' => 'nagflux',
        'groupBy' =>  array ());
        $this->assertSame($rexpected, $rtarget);
    }
}
