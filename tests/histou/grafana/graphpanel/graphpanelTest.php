<?php

namespace tests\grafana;

class GraphpanelTest extends \MyPHPUnitFrameworkTestCase
{
    public function testCreateGraphPanel()
    {
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
        define('SHOW_LEGEND', false);
        define("DATABASE_TYPE", 'foo');
        define("ELASTICSEARCH_INDEX", 'bar');
        
        $this->expectException('\InvalidArgumentException');
        $d = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel('d1');
    }
}
