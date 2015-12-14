<?php

namespace tests\grafana;

class PanelTest extends \PHPUnit_Framework_TestCase
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

    public function testCreatePanel()
    {
		$panel1 = new \histou\grafana\GraphPanel('p1', 'test');
		$this->assertEquals('p1', $panel1->toArray()['title']);
		$panel1->setSpan(20);
		$this->assertEquals(20, $panel1->toArray()['span']);
		$panel1->setEditable(false);
		$this->assertEquals(false, $panel1->toArray()['editable']);
		$panel1->setCustomProperty('foo','bar');
		$this->assertEquals('bar', $panel1->toArray()['foo']);
	}
}