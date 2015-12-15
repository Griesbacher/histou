<?php

namespace tests\template;

class TemplateTest extends \MyPHPUnitFrameworkTestCase
{
    protected function setUp()
    {
		define('INFLUX_FIELDSEPERATOR', '&');
    }

    public function testMatchesTablename()
    {
		$t = new \histou\template\Template(
			'file',
			new \histou\template\Rule('host', 'service', 'command', array('p1','p2'), 'file'),
			function ($perfData) { return 'foo'; }
		);

		$this->assertSame(true, $t->matchesTablename('host&service&command&p1&value'));
		$this->assertSame(false, $t->matchesTablename('horst&service&command&p1&value'));
		$this->assertSame(false, $t->matchesTablename('host&service&command&p3&value'));
    }



    public function testValidTemplate()
    {
		$validTests = array(
			array(new \histou\template\Rule('host', 'service', 'command', array(), 'file'), false),
			array(new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2'), 'file'), false),
			array(new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), 'file'), true),
			array(new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3', 'p4'), 'file'), false),
			array(new \histou\template\Rule('host', 'service', 'command', array('.*'), 'file'), true),
			array(new \histou\template\Rule('host', 'service', 'foo', array('.*'), 'file'), false),
		);
		\histou\template\Rule::setCheck(
			'host',
			'service',
			'command',
			array('p1', 'p2', 'p3')
		);

		foreach($validTests as $test){
			$t = new \histou\template\Template(
				'file',
				$test[0],
				function ($perfData) { return 'foo'; }
			);
			$this->assertSame($test[1], $t->isValid());
		}
	}
}
