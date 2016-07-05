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
        
        $t3 = $gpanel->addWarnToTarget($t1, '', false);
        $this->assertSame(4, sizeof($t2['metrics']));
        $this->assertSame('value', $t2['metrics'][0]['field']);
        $this->assertSame('warn', $t2['metrics'][1]['field']);
        $this->assertSame('warn-min', $t2['metrics'][2]['field']);
        $this->assertSame('warn-max', $t2['metrics'][3]['field']);

        $t4 = $gpanel->addCritToTarget($t1);
        $this->assertSame(4, sizeof($t4['metrics']));
        $this->assertSame('value', $t4['metrics'][0]['field']);
        $this->assertSame('crit', $t4['metrics'][1]['field']);
        $this->assertSame('crit-min', $t4['metrics'][2]['field']);
        $this->assertSame('crit-max', $t4['metrics'][3]['field']);
        
        $t5 = $gpanel->addCritToTarget($t1, '', false);
        $this->assertSame(4, sizeof($t5['metrics']));
        $this->assertSame('value', $t5['metrics'][0]['field']);
        $this->assertSame('crit', $t5['metrics'][1]['field']);
        $this->assertSame('crit-min', $t5['metrics'][2]['field']);
        $this->assertSame('crit-max', $t5['metrics'][3]['field']);
        
        $d1 = $gpanel->genDowntimeTarget('h1', 's1', 'c1', 'p1');
        $this->assertSame('downtime', $d1['alias']);
        $this->assertSame('host: "h1" AND service: "s1" AND command: "c1" AND performanceLabel: "p1" AND downtime: "true"', $d1['query']);
        try {
            $d1 = $gpanel->genForecastTarget('h1', 's1', 'c1', 'p1');
        } catch (\Exception $e) {
            $this->assertEquals("Not implemented", $e->getMessage());
        }
    }
}
