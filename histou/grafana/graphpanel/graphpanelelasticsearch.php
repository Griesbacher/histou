<?php
/**
Contains types of Panels.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\grafana\graphpanel;

/**
Base Panel.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class GraphPanelElasticsearch extends GraphPanel
{
    /**
    Constructor.
    @param string  $title      name of the panel.
    @param boolean $legendShow hide the legend or not
    @return object.
    **/
    public function __construct($title, $legendShow = SHOW_LEGEND, $id = -1)
    {
        parent::__construct($title, 'graph', $id);
    }

    private function createTarget(array $filterTags = array())
    {
        return array(
                    'query' => $this->createQuery($filterTags),
                    'metrics' => array(),
                    'dsType' => 'elasticsearch',
                    'resultFormat' => 'time_series',
                    'datasource' => ELASTICSEARCH_INDEX,
                    'bucketAggs' => array(array(
                                                'field' => 'timestamp',
                                                'id' => '2',
                                                'settings' => array('interval' => 'auto', 'min_doc_count' => 0),
                                                'type' => 'date_histogram'
                                    )),
                    'groupBy' => array(
                                        array('params' => array('$interval'), 'type' => 'time'),
                                        array('params' => array('null'), 'type' => 'fill'),
                                ),
                    'select' => array(array(
                                            array('params' => array('value'), 'type' => 'field'),
                                            array('params' => array(), 'type' => 'mean')
                                )),
                    'tags' => array(),
                    );
    }

    private function createQuery(array $filterTags)
    {
        $result = "";
        foreach ($filterTags as $key => $value) {
            $result = sprintf('%s%s: "%s" AND ', $result, $key, $value);
        }
        return substr($result, 0, -5);
    }

    /**
    This creates a target with an value.
    **/
    public function genTargetSimple($host, $service, $command, $performanceLabel, $color = '#085DFF', $alias = '', $useRegex = false)
    {
        if ($alias == '') {
            $alias = $performanceLabel;
        }
        $target = $this->createTarget(array(
                                            'host' => $host,
                                            'service' => $service,
                                            'command' => $command,
                                            'performanceLabel' => $performanceLabel
                                            ));
        $target['alias'] = $alias.'-{{field}}';
        $target = $this->addXToTarget($target, array('value'), $alias, $color);
        return $target;
    }

    public function addWarnToTarget($target, $alias = '', $color = true)
    {
        if ($color) {
            return $this->addXToTarget($target, array('warn', 'warn-min', 'warn-max'), $alias, '#FFFC15');
        }
        return $this->addXToTarget($target, array('warn', 'warn-min', 'warn-max'), $alias, '');
    }

    public function addCritToTarget($target, $alias = '', $color = true)
    {
        if ($color) {
            return $this->addXToTarget($target, array('crit', 'crit-min', 'crit-max'), $alias, '#FF3727');
        }
        return $this->addXToTarget($target, array('crit', 'crit-min', 'crit-max'), $alias, '');
    }

    private function addXToTarget($target, array $types, $alias, $color, $keepAlias = false)
    {
        foreach ($types as $type) {
            if ($keepAlias) {
                $newalias = $alias;
            } else {
                $newalias = $alias.'-'.$type;
            }
            array_push($target['metrics'], $this->createMetric($type, sizeof($target['metrics'])));
            if ($color != '') {
                $this->addAliasColor($newalias, $color);
            }
        }
        return $target;
    }

    private function createMetric($target, $index)
    {
        return array('field' => $target, 'id' => $index, 'meta' => array(), 'settings' => array(), 'type' => 'avg');
    }

    /**
    This creates a target for an downtime.
    **/
    public function genDowntimeTarget($host, $service, $command, $performanceLabel, $alias = '', $useRegex = false)
    {
        if ($alias == '') {
            $alias = 'downtime';
        }
        $target = $this->createTarget(
            array(
                    'host' => $host,
                    'service' => $service,
                    'command' => $command,
                    'performanceLabel' => $performanceLabel,
                    'downtime' => 'true'
                )
        );
        $target['alias'] = $alias;
        $target = $this->addXToTarget($target, array('value'), $alias, '#EEE', true);
        $this->addToSeriesOverrides(
            array(
                'lines' => true,
                'alias' => $alias,
                'linewidth' => 3,
                'legend' => false,
                'fill' => 3,
            )
        );
        return $target;
    }
}
