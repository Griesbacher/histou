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
        define('INFLUX_FIELDSEPERATOR', '&');
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

		$gpanel->addAliasColor('alias1', '#123');
        $this->assertEquals(1, sizeof($gpanel->toArray()['aliasColors']));
        $this->assertEquals('#123', $gpanel->toArray()['aliasColors']['alias1']);

		$gpanel->setleftYAxisLabel('ms');
        $this->assertEquals('ms', $gpanel->toArray()['leftYAxisLabel']);

		$this->assertEquals(2, sizeof($gpanel->toArray()['targets']));
		$gpanel->addWarning('host0', 'service1', 'command2', 'perfLabel3%');
		$this->assertEquals(5, sizeof($gpanel->toArray()['targets']));
		$this->assertEquals(
		'host0&service1&command2&perfLabel3%&warn',
		$gpanel->toArray()['targets'][2]['measurement']
		);
		$this->assertEquals(
		array(array('key'=>'type','operator'=>'=', 'value' => 'normal')),
		$gpanel->toArray()['targets'][2]['tags']
		);
		$this->assertEquals(
		'select mean(value) from "host0&service1&command2&perfLabel3%&warn" where AND $timeFilter group by time($interval)',
		$gpanel->toArray()['targets'][3]['query']
		);
		$this->assertEquals(
		array(array('key'=>'type','operator'=>'=', 'value' => 'min')),
		$gpanel->toArray()['targets'][3]['tags']
		);
		$this->assertEquals('warn-max',$gpanel->toArray()['targets'][4]['alias']);
		$this->assertEquals('#FFFC15',$gpanel->toArray()['aliasColors']['warn-max']);

		$this->assertEquals(5, sizeof($gpanel->toArray()['targets']));
		$gpanel->addCritical('host&0', 'service1', 'command2', 'perfLabel3%');
		$this->assertEquals(8, sizeof($gpanel->toArray()['targets']));
		$this->assertEquals('#FF3727',$gpanel->toArray()['aliasColors']['crit']);

		$gpanel->setLinewidth(10);
		$this->assertEquals(10, $gpanel->toArray()['linewidth']);

		$this->assertEquals(0, sizeof($gpanel->toArray()['seriesOverrides']));
		$this->assertEquals(8, sizeof($gpanel->toArray()['targets']));
		$gpanel->addDowntime('host0', 'service1', 'command2', 'perfLabel3');
		$this->assertEquals(1, sizeof($gpanel->toArray()['seriesOverrides']));
		$this->assertEquals(9, sizeof($gpanel->toArray()['targets']));
		$this->assertEquals(
		array(array('key'=>'downtime','operator'=>'=', 'value' => '1')),
		$gpanel->toArray()['targets'][8]['tags']
		);
		$this->assertEquals(
		array('lines' => true,'alias' => 'downtime','linewidth' => 3,'legend' => false,'fill' => 3),
		$gpanel->toArray()['seriesOverrides'][0]
		);

		$this->assertEquals(1, sizeof($gpanel->toArray()['seriesOverrides']));
		$gpanel->fillBelowLine('foo', 1);
		$this->assertEquals(2, sizeof($gpanel->toArray()['seriesOverrides']));
		$this->assertEquals(
		array('alias' => 'foo', 'fill' => 1),
		$gpanel->toArray()['seriesOverrides'][1]
		);
    }
}
