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
class GraphPanelInfluxdb extends GraphPanel
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
                    'measurement' => 'metrics',
                    'alias' => '$col',
                    'select' => array(),
                    'tags' => $this->createFilterTags($filterTags),
                    'dsType' => 'influxdb',
                    'resultFormat' => 'time_series',
                    'datasource' => INFLUXDB_DB
                    );
    }

    /**
    Creates filter tags array based on host, service...
    **/
    private function createFilterTags(array $filterTags = array())
    {
        $tags = array();
        $i = 0;
        foreach ($filterTags as $key => $value) {
            foreach ($value as $type => &$typeValue) { //Used for Backslash in tag value
                if (!\histou\helper\str::isRegex($typeValue)) {
                    $typeValue = str_replace('\\', '\\\\', $typeValue);
                }
            }
            $condition = (array_key_exists('condition', $value) ? $value['condition'] : 'AND');
            $operator = (array_key_exists('operator', $value) ? $value['operator'] : '=');
            if ($i == 0) {
                array_push($tags, array('key'=> $key, 'operator' => $operator, 'value' => $value['value'] ));
            } else {
                array_push($tags, array('condition' => $condition, 'key'=> $key, 'operator' => $operator, 'value' => $value['value']));
            }
            $i++;
        }
        return $tags;
    }

    /**
    This creates a target with an value.
    **/
    public function genTargetSimple($host, $service, $command, $performanceLabel, $color = '#085DFF', $alias = '')
    {
        if ($alias == '') {
            $alias = $performanceLabel;
        }
        $target = $this->createTarget(array(
                                            'host' => array('value' => $host),
                                            'service' => array('value' => $service),
                                            'command' => array('value' => $command),
                                            'performanceLabel' => array('value' => $performanceLabel)
                                            ));
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
            array_push($target['select'], $this->createSelect($type, $newalias));
            if ($color != '') {
                $this->addAliasColor($newalias, $color);
            }
        }
        return $target;
    }

    private function createSelect($name, $alias)
    {
        return array(
                    array('type' => 'field', 'params' => array($name)),
                    array('type' => 'mean', 'params' => array()),
                    array('type' => 'alias', 'params' => array($alias))
                    );
    }
    /**
    This creates a target for an downtime.
    **/
    public function genDowntimeTarget($host, $service, $command, $performanceLabel, $alias = '')
    {
        if ($alias == '') {
            $alias = 'downtime';
        }
        $target = $this->createTarget(
            array(
                    'host' => array('value' => $host),
                    'service' => array('value' => $service),
                    'command' => array('value' => $command),
                    'performanceLabel' => array('value' => $performanceLabel),
                    'downtime' => array('value' => "true")
                )
        );
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
