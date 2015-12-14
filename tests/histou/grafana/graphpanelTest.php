<?php

namespace tests\grafana;

class GraphpanelTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        spl_autoload_register('__autoload');
    }

    public function init()
    {
        $_GET['host'] = 'host';
        \histou\Basic::parsArgs();
        define('INFLUX_DB', 'nagflux');
    }

    public function testCreateGraphPanel()
    {
        $this->init();
        $gpanel = new \histou\grafana\GraphPanel('gpanel');
        $this->assertEquals(2, $gpanel->toArray()['linewidth']);

        $gpanel->setTooltip(array(true));
        $this->assertEquals(array(true), $gpanel->toArray()['tooltip']);

        $this->assertEquals(0, sizeof($gpanel->toArray()['targets']));
        $gpanel->addTargetSimple("target1", "alias1", array('tag1', 'tag2'));
        $this->assertEquals(1, sizeof($gpanel->toArray()['targets']));
        $this->assertEquals('alias1', $gpanel->toArray()['targets'][0]['alias']);
        $this->assertEquals('nagflux', $gpanel->toArray()['targets'][0]['datasource']);
        $this->assertEquals(array('tag1', 'tag2'), $gpanel->toArray()['targets'][0]['tags']);

        $gpanel->addTargetSimple("target2");
        $this->assertEquals(2, sizeof($gpanel->toArray()['targets']));
        $this->assertEquals('', $gpanel->toArray()['targets'][1]['alias']);
        $this->assertEquals(array(), $gpanel->toArray()['targets'][1]['tags']);
    }
}
