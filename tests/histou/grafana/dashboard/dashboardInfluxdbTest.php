<?php

namespace tests\grafana;

class DashboardInfluxDBTest extends \MyPHPUnitFrameworkTestCase
{
    public function testCreateDashboardInfluxdb()
    {
        define("INFLUXDB_DB", 'nagflux');
        define("DATABASE_TYPE", 'influxdb');
        define('INFLUXDB', 'influxdb');

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

    public function testToArrayInfluxdb()
    {
        define("INFLUXDB_DB", 'nagflux');
        define("DATABASE_TYPE", 'influxdb');
        define('INFLUXDB', 'influxdb');
        define('HEIGHT', '100');
        define('SHOW_LEGEND', true);
        define('FORECAST_DATASOURCE_NAME', "nagflux_forecast");

        $d = \histou\grafana\dashboard\DashboardFactory::generateDashboard('d');
        $row = new \histou\grafana\Row("r");
        $gpanel = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel("p");
        \histou\template\ForecastTemplate::$config = array (
                                                        'size' =>  array (
                                                            'method' => 'SimpleLinearRegression',
                                                            'forecast' => '20m',
                                                        ),
                                                        'time' =>  array (
                                                            'method' => 'SimpleLinearRegression',
                                                            'forecast' => '30m',
                                                        ),
                                                    );
        $target = $gpanel->genForecastTarget('host', 'service', 'command', 'size', '000', '', true, true);
        $target = $gpanel->genForecastTarget('host', 'service', 'command', 'time', '000', '', true, true);
        $a = $d->toArray();
        $expected = array (
                          'id' => '1',
                          'title' => 'd',
                          'originalTitle' => 'CustomDashboard',
                          'tags' => array (),
                          'timezone' => 'browser',
                          'editable' => true,
                          'hideControls' => true,
                          'sharedCrosshair' => false,
                          'nav' =>
                          array (
                            array (
                              'type' => 'timepicker',
                              'enable' => true,
                              'time_options' =>
                              array (
                                0 => '5m',
                                1 => '15m',
                                2 => '1h',
                                3 => '6h',
                                4 => '12h',
                                5 => '24h',
                                6 => '2d',
                                7 => '7d',
                                8 => '30d',
                              ),
                              'refresh_intervals' =>
                              array (
                                0 => '5s',
                                1 => '10s',
                                2 => '30s',
                                3 => '1m',
                                4 => '5m',
                                5 => '15m',
                                6 => '30m',
                                7 => '1h',
                                8 => '2h',
                                9 => '1d',
                              ),
                              'now' => true,
                              'collapse' => false,
                              'notice' => false,
                            ),
                          ),
                          'time' =>
                          array (
                            'from' => 'now-8h',
                            'to' => 'now+30m',
                          ),
                          'templating' =>
                          array ( 'list' => array ()),
                          'annotations' => array ('enable' => true, 'list' =>array ()),
                          'refresh' => '30s',
                          'version' => '6',
                          'rows' =>array (),
                        );
        $this->assertSame($expected, $a);

    }
}
