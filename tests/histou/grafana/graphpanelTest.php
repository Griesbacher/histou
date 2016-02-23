<?php

namespace tests\grafana;

class GraphpanelTest extends \MyPHPUnitFrameworkTestCase
{
    public function init()
    {
        $_GET['host'] = 'host';
        \histou\Basic::parsIni('histou.ini.example');
        \histou\Basic::parsArgs();
    }

    public function testCreateGraphPanelInfluxdb()
    {
        $this->init();
        $gpanel = new \histou\grafana\GraphPanelInfluxdb('gpanel');
        $this->assertSame(2, $gpanel->toArray()['linewidth']);

        $gpanel->setTooltip(array(true));
        $this->assertSame(array(true), $gpanel->toArray()['tooltip']);

        $this->assertSame(0, sizeof($gpanel->toArray()['seriesOverrides']));
        $gpanel->addRegexColor('/.*/', '#FFF');
        $this->assertSame(1, sizeof($gpanel->toArray()['seriesOverrides']));
        $this->assertSame('/.*/', $gpanel->toArray()['seriesOverrides'][0]['alias']);
        $gpanel->addRegexColor('/-value', '#FFF');
        $this->assertSame(2, sizeof($gpanel->toArray()['seriesOverrides']));
        $this->assertSame('/\/-value/', $gpanel->toArray()['seriesOverrides'][1]['alias']);

        $gpanel->addAliasColor('foo', '#123');
        $this->assertSame(1, sizeof($gpanel->toArray()['aliasColors']));
        $this->assertSame('#123', $gpanel->toArray()['aliasColors']['foo']);

        $gpanel->setleftYAxisLabel('ms');
        $this->assertSame('ms', $gpanel->toArray()['leftYAxisLabel']);

        //Convert Unit
        //left
        $gpanel->setLeftUnit('%');
        $this->assertSame(array('percent', 'short'), $gpanel->toArray()['y_formats']);
        $gpanel->setLeftUnit('s');
        $this->assertSame(array('s', 'short'), $gpanel->toArray()['y_formats']);
        $gpanel->setLeftUnit('foo');
        $this->assertSame(array('short', 'short'), $gpanel->toArray()['y_formats']);
        $this->assertSame('foo', $gpanel->toArray()['leftYAxisLabel']);
        //right
        $gpanel->setRightUnit('b');
        $this->assertSame(array('short', 'bits'), $gpanel->toArray()['y_formats']);
        $gpanel->setRightUnit('B');
        $this->assertSame(array('short', 'bytes'), $gpanel->toArray()['y_formats']);
        $gpanel->setRightUnit('bar');
        $this->assertSame('bar', $gpanel->toArray()['rightYAxisLabel']);
        $gpanel = new \histou\grafana\GraphPanelInfluxdb('gpanel');
        $gpanel->setRightUnit('kB');
        $this->assertSame(array('short', 'kbytes'), $gpanel->toArray()['y_formats']);
        $gpanel->setRightUnit('MB');
        $this->assertSame(array('short', 'mbytes'), $gpanel->toArray()['y_formats']);
        $gpanel->setRightUnit('GiB');
        $this->assertSame(array('short', 'gbytes'), $gpanel->toArray()['y_formats']);

        //Linewidth
        $gpanel->setLinewidth(10);
        $this->assertSame(10, $gpanel->toArray()['linewidth']);

        //Fill below
        $this->assertSame(0, sizeof($gpanel->toArray()['seriesOverrides']));
        $gpanel->fillBelowLine('foo', 1);
        $this->assertSame(1, sizeof($gpanel->toArray()['seriesOverrides']));

        //Negate Y
        $this->assertSame(1, sizeof($gpanel->toArray()['seriesOverrides']));
        $gpanel->negateY('foo');
        $this->assertSame(2, sizeof($gpanel->toArray()['seriesOverrides']));
        $this->assertSame(
            array('alias' => 'foo', 'transform' => 'negative-Y'),
            $gpanel->toArray()['seriesOverrides'][1]
        );

        //setYAxis
        $this->assertSame(2, sizeof($gpanel->toArray()['seriesOverrides']));
        $gpanel->setYAxis('foo');
        $gpanel->setYAxis('bar', 2);
        $this->assertSame(4, sizeof($gpanel->toArray()['seriesOverrides']));
        $this->assertSame(
            array('alias' => 'foo', 'yaxis' => 1),
            $gpanel->toArray()['seriesOverrides'][2]
        );
        $this->assertSame(
            array('alias' => 'bar', 'yaxis' => 2),
            $gpanel->toArray()['seriesOverrides'][3]
        );

        $target1 = $gpanel->genTargetSimple('host', 'service', 'command', 'perfLabel');
        $expected = array('measurement' => 'metrics', 'alias' => '$col',
                            'select' =>  array(array(array(
                                                        'type' => 'field',
                                                        'params' => array ('value')
                                                            ),
                                                    array (
                                                        'type' => 'mean',
                                                        'params' => array (),
                                                            ),
                                                    array (
                                                        'type' => 'alias',
                                                        'params' => array ('perfLabel-value'),
                                                            ),
                                                    ),
                                                ),
                            'tags' => array(array(
                                                'key' => 'host',
                                                'operator' => '=',
                                                'value' => 'host',
                                                ),
                                            array (
                                                'condition' => 'AND',
                                                'key' => 'service',
                                                'operator' => '=',
                                                'value' => 'service',
                                            ),
                                            array (
                                                'condition' => 'AND',
                                                'key' => 'command',
                                                'operator' => '=',
                                                'value' => 'command',
                                            ),
                                            array (
                                                'condition' => 'AND',
                                                'key' => 'performanceLabel',
                                                'operator' => '=',
                                                'value' => 'perfLabel',
                                            ),
                                        ),
                            'dsType' => 'influxdb', 'resultFormat' => 'time_series', 'datasource' => 'nagflux',
        );
        $this->assertSame($expected, $target1);

        $target1 = $gpanel->addWarnToTarget($target1);
        //$this->assertSame($expected, $target1);
        $expected  =   array (  array (     array ( 'type' => 'field','params' => array ('value')),
                                        array ('type' => 'mean','params' =>array ()),
                                        array ('type' => 'alias','params' =>array ('perfLabel-value')),
                                    ),
                                array (     array ('type' => 'field','params' =>array ('warn')),
                                        array ('type' => 'mean','params' =>array ()),
                                        array ('type' => 'alias','params' => array ('-warn')),
                                    ),
                                array (     array ('type' => 'field','params' =>array ('warn-min')),
                                        array ('type' => 'mean','params' =>array ()),
                                        array ('type' => 'alias','params' =>array ('-warn-min')),
                                    ),
                                array (     array ('type' => 'field','params' =>array ('warn-max')),
                                        array ('type' => 'mean','params' =>array ()),
                                        array ('type' => 'alias','params' =>array ('-warn-max')),
                                    ),
                            );
        $this->assertSame($expected, $target1['select']);
        $target2 = $gpanel->genTargetSimple('host', 'service', 'command', 'perfLabel');
        $target2 = $gpanel->addWarnToTarget($target2, 'alias123', false);
        $expected = array (
                          'measurement' => 'metrics',
                          'alias' => '$col',
                          'select' =>
                          array (
                            array (
                              array (
                                'type' => 'field',
                                'params' =>
                                array (
                                  'value',
                                ),
                              ),
                              array (
                                'type' => 'mean',
                                'params' =>
                                array (
                                ),
                              ),
                              array (
                                'type' => 'alias',
                                'params' =>
                                array (
                                 'perfLabel-value',
                                ),
                              ),
                            ),
                            array (
                              array (
                                'type' => 'field',
                                'params' =>
                                array (
                                 'warn',
                                ),
                              ),
                              array (
                                'type' => 'mean',
                                'params' =>
                                array (
                                ),
                              ),
                              array (
                                'type' => 'alias',
                                'params' =>
                                array (
                                 'alias123-warn',
                                ),
                              ),
                            ),
                            array (
                              array (
                                'type' => 'field',
                                'params' =>
                                array (
                                  'warn-min',
                                ),
                              ),
                              array (
                                'type' => 'mean',
                                'params' =>
                                array (
                                ),
                              ),
                              array (
                                'type' => 'alias',
                                'params' =>
                                array (
                                  'alias123-warn-min',
                                ),
                              ),
                            ),
                            array (
                              array (
                                'type' => 'field',
                                'params' =>
                                array (
                                  'warn-max',
                                ),
                              ),
                              array (
                                'type' => 'mean',
                                'params' =>
                                array (
                                ),
                              ),
                              array (
                                'type' => 'alias',
                                'params' =>
                                array (
                                  'alias123-warn-max',
                                ),
                              ),
                            ),
                          ),
                          'tags' =>
                          array (
                            array (
                              'key' => 'host',
                              'operator' => '=',
                              'value' => 'host',
                            ),
                            array (
                              'condition' => 'AND',
                              'key' => 'service',
                              'operator' => '=',
                              'value' => 'service',
                            ),
                            array (
                              'condition' => 'AND',
                              'key' => 'command',
                              'operator' => '=',
                              'value' => 'command',
                            ),
                            array (
                              'condition' => 'AND',
                              'key' => 'performanceLabel',
                              'operator' => '=',
                              'value' => 'perfLabel',
                            ),
                          ),
                          'dsType' => 'influxdb',
                          'resultFormat' => 'time_series',
                          'datasource' => 'nagflux',
                        );
        $this->assertSame($expected, $target2);
        $target3 = $gpanel->genTargetSimple('host', 'service', 'command', 'perfLabel');
        $target3 = $gpanel->addCritToTarget($target3);
        $expected = array(
                            array(
                                array(
                                    'type' => 'field',
                                    'params' => array(
                                        'value',
                                    ) ,
                                ) ,
                                array(
                                    'type' => 'mean',
                                    'params' => array() ,
                                ) ,
                                array(
                                    'type' => 'alias',
                                    'params' => array(
                                        'perfLabel-value',
                                    ) ,
                                ) ,
                            ) ,
                            array(
                                array(
                                    'type' => 'field',
                                    'params' => array(
                                        'crit',
                                    ) ,
                                ) ,
                                array(
                                    'type' => 'mean',
                                    'params' => array() ,
                                ) ,
                                array(
                                    'type' => 'alias',
                                    'params' => array(
                                        '-crit',
                                    ) ,
                                ) ,
                            ) ,
                            array(
                                array(
                                    'type' => 'field',
                                    'params' => array(
                                        'crit-min',
                                    ) ,
                                ) ,
                                array(
                                    'type' => 'mean',
                                    'params' => array() ,
                                ) ,
                                array(
                                    'type' => 'alias',
                                    'params' => array(
                                        '-crit-min',
                                    ) ,
                                ) ,
                            ) ,
                            array(
                                array(
                                    'type' => 'field',
                                    'params' => array(
                                        'crit-max',
                                    ) ,
                                ) ,
                                array(
                                    'type' => 'mean',
                                    'params' => array() ,
                                ) ,
                                array(
                                    'type' => 'alias',
                                    'params' => array(
                                        '-crit-max',
                                    ) ,
                                ) ,
                            ) ,
                        );
        $this->assertSame($expected, $target3['select']);
        $target4 = $gpanel->genTargetSimple('host', 'service', 'command', 'perfLabel');
        $target4 = $gpanel->addCritToTarget($target4, 'alias123', false);
        $expected = array(
                        array(
                            array(
                                'type' => 'field',
                                'params' => array(
                                    'value',
                                ) ,
                            ) ,
                            array(
                                'type' => 'mean',
                                'params' => array() ,
                            ) ,
                            array(
                                'type' => 'alias',
                                'params' => array(
                                    'perfLabel-value',
                                ) ,
                            ) ,
                        ) ,
                        array(
                            array(
                                'type' => 'field',
                                'params' => array(
                                    'crit',
                                ) ,
                            ) ,
                            array(
                                'type' => 'mean',
                                'params' => array() ,
                            ) ,
                            array(
                                'type' => 'alias',
                                'params' => array(
                                    'alias123-crit',
                                ) ,
                            ) ,
                        ) ,
                        array(
                            array(
                                'type' => 'field',
                                'params' => array(
                                    'crit-min',
                                ) ,
                            ) ,
                            array(
                                'type' => 'mean',
                                'params' => array() ,
                            ) ,
                            array(
                                'type' => 'alias',
                                'params' => array(
                                    'alias123-crit-min',
                                ) ,
                            ) ,
                        ) ,
                        array(
                            array(
                                'type' => 'field',
                                'params' => array(
                                    'crit-max',
                                ) ,
                            ) ,
                            array(
                                'type' => 'mean',
                                'params' => array() ,
                            ) ,
                            array(
                                'type' => 'alias',
                                'params' => array(
                                    'alias123-crit-max',
                                ) ,
                            ) ,
                        ) ,
                    );
        $this->assertSame($expected, $target4['select']);
        $downtime1 = $gpanel->genDowntimeTarget('host', 'service', 'command', 'perfLabel');
        $expectedDowntime = array(
                                'measurement' => 'metrics',
                                'alias' => '$col',
                                'select' => array(
                                    array(
                                        array(
                                            'type' => 'field',
                                            'params' => array(
                                                'value',
                                            ) ,
                                        ) ,
                                        array(
                                            'type' => 'mean',
                                            'params' => array() ,
                                        ) ,
                                        array(
                                            'type' => 'alias',
                                            'params' => array(
                                                'downtime',
                                            ) ,
                                        ) ,
                                    ) ,
                                ) ,
                                'tags' => array(
                                    array(
                                        'key' => 'host',
                                        'operator' => '=',
                                        'value' => 'host',
                                    ) ,
                                    array(
                                        'condition' => 'AND',
                                        'key' => 'service',
                                        'operator' => '=',
                                        'value' => 'service',
                                    ) ,
                                    array(
                                        'condition' => 'AND',
                                        'key' => 'command',
                                        'operator' => '=',
                                        'value' => 'command',
                                    ) ,
                                    array(
                                        'condition' => 'AND',
                                        'key' => 'performanceLabel',
                                        'operator' => '=',
                                        'value' => 'perfLabel',
                                    ) ,
                                    array(
                                        'condition' => 'AND',
                                        'key' => 'downtime',
                                        'operator' => '=',
                                        'value' => 'true',
                                    ) ,
                                ) ,
                                'dsType' => 'influxdb',
                                'resultFormat' => 'time_series',
                                'datasource' => 'nagflux',
                            );
        $this->assertSame($expectedDowntime, $downtime1);
        $downtime2 = $gpanel->genDowntimeTarget('host', 'service', 'command', 'perfLabel', 'alias123');
        $expectedDowntime = array(
                                'measurement' => 'metrics',
                                'alias' => '$col',
                                'select' => array(
                                    array(
                                        array(
                                            'type' => 'field',
                                            'params' => array(
                                                'value',
                                            ) ,
                                        ) ,
                                        array(
                                            'type' => 'mean',
                                            'params' => array() ,
                                        ) ,
                                        array(
                                            'type' => 'alias',
                                            'params' => array(
                                                'alias123',
                                            ) ,
                                        ) ,
                                    ) ,
                                ) ,
                                'tags' => array(
                                    array(
                                        'key' => 'host',
                                        'operator' => '=',
                                        'value' => 'host',
                                    ) ,
                                    array(
                                        'condition' => 'AND',
                                        'key' => 'service',
                                        'operator' => '=',
                                        'value' => 'service',
                                    ) ,
                                    array(
                                        'condition' => 'AND',
                                        'key' => 'command',
                                        'operator' => '=',
                                        'value' => 'command',
                                    ) ,
                                    array(
                                        'condition' => 'AND',
                                        'key' => 'performanceLabel',
                                        'operator' => '=',
                                        'value' => 'perfLabel',
                                    ) ,
                                    array(
                                        'condition' => 'AND',
                                        'key' => 'downtime',
                                        'operator' => '=',
                                        'value' => 'true',
                                    ) ,
                                ) ,
                                'dsType' => 'influxdb',
                                'resultFormat' => 'time_series',
                                'datasource' => 'nagflux',
                            );
        $this->assertSame($expectedDowntime, $downtime2);
        $this->assertSame(0, sizeof($gpanel->toArray()['targets']));
        $gpanel->addTarget($target1);
        $this->assertSame(1, sizeof($gpanel->toArray()['targets']));
        $gpanel->addTarget($downtime1);
        $this->assertSame(2, sizeof($gpanel->toArray()['targets']));
        $this->assertSame($downtime1, $gpanel->toArray()['targets'][1]);
    }
}
