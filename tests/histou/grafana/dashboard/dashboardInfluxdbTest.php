<?php

namespace tests\grafana;

class DashboardInfluxDBTest extends \MyPHPUnitFrameworkTestCase
{
    public function testCreateDashboardInfluxdb()
    {
        define("INFLUXDB_DB", 'nagflux');
        define("DATABASE_TYPE", 'influxdb');
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
        
        $d = \histou\grafana\dashboard\DashboardFactory::generateDashboard('d1');
        $this->assertSame('d1', $d->toArray()['title']);

        $d->addTemplateForPerformanceLabel('name', 'h1', 's1', '.*', true, true);
        $this->assertSame(1, sizeof($d->toArray()['templating']['list']));
        $this->assertSame(true, $d->toArray()['templating']['list'][0]['multi']);
        $this->assertSame(true, $d->toArray()['templating']['list'][0]['includeAll']);
        $this->assertSame('name', $d->toArray()['templating']['list'][0]['name']);
        $this->assertSame('.*', $d->toArray()['templating']['list'][0]['regex']);
        $this->assertSame(INFLUXDB_DB, $d->toArray()['templating']['list'][0]['datasource']);
        $this->assertSame("SHOW TAG VALUES WITH KEY = \"performanceLabel\" WHERE \"host\" = 'h1' AND \"service\" = 's1'", $d->toArray()['templating']['list'][0]['query']);

        $d->addTemplateForPerformanceLabel('name', 'h1', 's1', '.*', false, false);
        $this->assertSame(false, $d->toArray()['templating']['list'][1]['multi']);
        $this->assertSame(false, $d->toArray()['templating']['list'][1]['includeAll']);
        $this->assertSame(2, sizeof($d->toArray()['templating']['list']));

        $this->assertSame('[[foo]]', $d->genTemplateVariable('foo'));
    }
}
