<?php

namespace tests\grafana;

class DashboardTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        spl_autoload_register('__autoload');
    }

    public function testCreatePanel()
    {
		define("SHOW_ANNOTATION", false);
		define("INFLUX_DB", 'nagflux');
		$d = new \histou\grafana\Dashboard('d1');
		$this->assertSame('d1', $d->toArray()['title']);

		$d->setEditable(false);
		$this->assertSame(false, $d->toArray()['editable']);

		$d->setCustomProperty('foo','bar');
		$this->assertSame('bar', $d->toArray()['foo']);

		$this->assertSame(0, sizeof($d->toArray()['annotations']['list']));
		$d->addAnnotation('aname', 'host0', 'service1', '#123', '#234', true, 1, 'foo');
		$this->assertSame(1, sizeof($d->toArray()['annotations']['list']));
		$this->assertSame('SELECT * FROM "host0&service1&messages" WHERE "type" = \'aname\' AND $timeFilter', $d->toArray()['annotations']['list'][0]['query']);

		$this->assertSame(1, sizeof($d->toArray()['annotations']['list']));
		$d->addDefaultAnnotations('host1', 'service2');
		$this->assertSame(6, sizeof($d->toArray()['annotations']['list']));
		$this->assertSame('downtime', $d->toArray()['annotations']['list'][5]['name']);
		$this->assertSame('#A218E8', $d->toArray()['annotations']['list'][5]['iconColor']);
		$this->assertSame('#A218E8', $d->toArray()['annotations']['list'][5]['lineColor']);
	}
}