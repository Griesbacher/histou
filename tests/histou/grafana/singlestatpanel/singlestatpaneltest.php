<?php

namespace tests\grafana;

class SinglestatpanelTest extends \MyPHPUnitFrameworkTestCase
{
    public function testCreateWrongGraphPanel()
    {
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
        define('SHOW_LEGEND', false);
        define("DATABASE_TYPE", 'foo');
        define("ELASTICSEARCH_INDEX", 'bar');
        
        $this->setExpectedException('\InvalidArgumentException');
        $d = \histou\grafana\singlestatpanel\SinglestatPanelFactory::generatePanel('d1');
    }
    public function testCreateElesticsearchGraphPanel()
    {
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
        define('SHOW_LEGEND', false);
        define("DATABASE_TYPE", 'elasticsearch');
        define("ELASTICSEARCH_INDEX", 'bar');
        
        $this->setExpectedException('\InvalidArgumentException');
        $d = \histou\grafana\singlestatpanel\SinglestatPanelFactory::generatePanel('d1');
    }
}
