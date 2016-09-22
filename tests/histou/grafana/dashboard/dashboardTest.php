<?php

namespace tests\grafana;

class DashboardTest extends \MyPHPUnitFrameworkTestCase
{
    public function testCreateDashboardInfluxdb()
    {
        define("SHOW_ANNOTATION", false);
        define("INFLUXDB_DB", 'nagflux');
        $d = new \histou\grafana\dashboard\DashboardInfluxdb('d1');
        $this->assertSame('d1', $d->toArray()['title']);

        $d->setEditable(false);
        $this->assertSame(false, $d->toArray()['editable']);

        $d->setCustomProperty('foo', 'bar');
        $this->assertSame('bar', $d->toArray()['foo']);

        $this->assertSame(0, sizeof($d->toArray()['annotations']['list']));
        $d->addAnnotation('aname', 'query!!', 'title', 'text', 'tags', '#123', '#234', 'datasource?', true, 1, 10);
        $this->assertSame(1, sizeof($d->toArray()['annotations']['list']));
        $this->assertSame(array (
                                  'datasource' => 'datasource?',
                                  'enable' => true,
                                  'iconColor' => '#123',
                                  'iconSize' => 1,
                                  'lineColor' => '#234',
                                  'name' => 'aname',
                                  'query' => 'query!!',
                                  'showLine' => true,
                                  'tagsColumn' => 'tags',
                                  'textColumn' => 'text',
                                  'titleColumn' => 'title',
                                ), $d->toArray()['annotations']['list'][0]);

        $this->assertSame(1, sizeof($d->toArray()['annotations']['list']));
        $d->addDefaultAnnotations('host1', 'service2');
        $this->assertSame(6, sizeof($d->toArray()['annotations']['list']));
        $this->assertSame('downtime', $d->toArray()['annotations']['list'][5]['name']);
        $this->assertSame('#A218E8', $d->toArray()['annotations']['list'][5]['iconColor']);
        $this->assertSame('#A218E8', $d->toArray()['annotations']['list'][5]['lineColor']);

        $this->assertSame(0, sizeof($d->toArray()['templating']['list']));
        $d->addTemplate('influx', 'TEMP', 'show keys', '.*', false, true);
        $this->assertSame(1, sizeof($d->toArray()['templating']['list']));
        $this->assertSame(true, $d->toArray()['templating']['enable']);
        $this->assertSame('TEMP', $d->toArray()['templating']['list'][0]['name']);
    }
    
    public function testInvalidDatabase()
    {
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
        define("DATABASE_TYPE", 'foo');
        define("ELASTICSEARCH_INDEX", 'bar');
        $this->setExpectedException('\InvalidArgumentException');
        $d = \histou\grafana\dashboard\DashboardFactory::generateDashboard('d1');
    }
}
