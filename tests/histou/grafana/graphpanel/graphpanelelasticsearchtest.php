<?php

namespace tests\grafana;

class GraphpanelTestElasticsearch extends \MyPHPUnitFrameworkTestCase
{
    public function testCreateGraphPanelElasticsearch()
    {
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
        define('SHOW_LEGEND', false);
        define("DATABASE_TYPE", ELASTICSEARCH);
        define("ELASTICSEARCH_INDEX", 'nagflux');
        
        $gpanel = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel('gpanel');
        $this->assertSame('gpanel', $gpanel->toArray()['title']);
        
        $t1 = $gpanel->genTargetSimple('h2', 's1', 'c1', 'p1');
        $this->assertSame('host: "h2" AND service: "s1" AND command: "c1" AND performanceLabel: "p1"', $t1['query']);
        $this->assertSame(ELASTICSEARCH_INDEX, $t1['datasource']);
        $this->assertSame('p1-{{field}}', $t1['alias']);
        $this->assertSame(1, sizeof($t1['metrics']));
        $t2 = $gpanel->addWarnToTarget($t1);
        $this->assertSame(4, sizeof($t2['metrics']));
        $this->assertSame('value', $t2['metrics'][0]['field']);
        $this->assertSame('warn', $t2['metrics'][1]['field']);
        $this->assertSame('warn-min', $t2['metrics'][2]['field']);
        $this->assertSame('warn-max', $t2['metrics'][3]['field']);
        $t3 = $gpanel->addCritToTarget($t1);
        $this->assertSame(4, sizeof($t3['metrics']));
        $this->assertSame('value', $t3['metrics'][0]['field']);
        $this->assertSame('crit', $t3['metrics'][1]['field']);
        $this->assertSame('crit-min', $t3['metrics'][2]['field']);
        $this->assertSame('crit-max', $t3['metrics'][3]['field']);
    }
}
