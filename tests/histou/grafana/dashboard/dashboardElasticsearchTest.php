<?php

namespace tests\grafana;

class DashboardElasticsearchTest extends \MyPHPUnitFrameworkTestCase
{
    public function testCreateDashboardElasticsearchh()
    {
        define("ELASTICSEARCH_INDEX", 'nagflux');
        define("DATABASE_TYPE", 'elasticsearch');
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
        $this->assertSame(ELASTICSEARCH_INDEX, $d->toArray()['templating']['list'][0]['datasource']);
        $this->assertSame('{"find": "terms", "field": "performanceLabel", "query": "host: h1, service: s1"}', $d->toArray()['templating']['list'][0]['query']);

        $d->addTemplateForPerformanceLabel('name', 'h1', 's1', '.*', false, false);
        $this->assertSame(false, $d->toArray()['templating']['list'][1]['multi']);
        $this->assertSame(false, $d->toArray()['templating']['list'][1]['includeAll']);
        $this->assertSame(2, sizeof($d->toArray()['templating']['list']));

        $this->assertSame('$foo', $d->genTemplateVariable('foo'));
    }
    
    public function testAddAnnotation(){
        define("ELASTICSEARCH_INDEX", 'nagflux');
        define("DATABASE_TYPE", 'elasticsearch');
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
        define('SHOW_ANNOTATION', true);
        
        $d = \histou\grafana\dashboard\DashboardFactory::generateDashboard('d1');
        //This should do nothing at the moment
        $d->addAnnotation('name', 'hostname', 'servicename', 'iconColor', 'lineColor');
        $this->assertSame($d, $d);
    }

        public function testAddDefaultAnnotations(){
        define("ELASTICSEARCH_INDEX", 'nagflux');
        define("DATABASE_TYPE", 'elasticsearch');
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
        define('SHOW_ANNOTATION', true);
        
        $d = \histou\grafana\dashboard\DashboardFactory::generateDashboard('d1');
        //This should do nothing at the moment
        $d->addDefaultAnnotations('name', 'hostname');
        $this->assertSame($d, $d);
    }
}
