<?php

namespace tests\grafana;

class GraphpanelTest extends \MyPHPUnitFrameworkTestCase
{
    public function testCreateGraphPanel()
    {
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
        define('SHOW_LEGEND', false);
        try {
            define("DATABASE_TYPE", 'foo');
            define("ELASTICSEARCH_INDEX", 'bar');
        
            $d = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel('d1');
        } catch (\InvalidArgumentException $e) {
            //TODO: add proper test
        }
    }
}
