<?php

namespace tests\grafana;

class GraphpanelTest extends \MyPHPUnitFrameworkTestCase
{
    public function testCreateGraphPanel()
    {
        define('INFLUXDB', 'influxdb');
        define('SHOW_LEGEND', false);
        define("DATABASE_TYPE", 'foo');

        $this->setExpectedException('\InvalidArgumentException');
        $d = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel('d1');
    }
}
