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
namespace histou\grafana\singlestatpanel;

/**
Base Panel.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class SinglestatPanelInfluxdb extends SinglestatPanel
{
    /**
    Constructor.
    @param string  $title      name of the panel.
    @param boolean $legendShow hide the legend or not
    @return object.
    **/
    public function __construct($title, $id = -1)
    {
        parent::__construct($title, 'graph', $id);
    }

    public function createTarget(array $filterTags = array(), $datasource = INFLUXDB_DB)
    {
        return array(
                    'measurement' => 'metrics',
                    'alias' => '$col',
                    'select' => array(),
                    'tags' => $this->createFilterTags($filterTags),
                    'dsType' => 'influxdb',
                    'resultFormat' => 'time_series',
                    'datasource' => $datasource,
                    'groupBy' => array(),
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
    public function genTargetSimple($host, $service, $command, $performanceLabel, $useRegex = false)
    {
        if ($useRegex) {
            $target = $this->createTarget(array(
                                            'host' => array('value' => \histou\helper\str::genRegex($host), 'operator' => '=~'),
                                            'service' => array('value' => \histou\helper\str::genRegex($service), 'operator' => '=~'),
                                            'command' => array('value' => \histou\helper\str::genRegex($command), 'operator' => '=~'),
                                            'performanceLabel' => array('value' => \histou\helper\str::genRegex($performanceLabel), 'operator' => '=~')
                                            ));
        } else {
            $target = $this->createTarget(array(
                                            'host' => array('value' => $host),
                                            'service' => array('value' => $service),
                                            'command' => array('value' => $command),
                                            'performanceLabel' => array('value' => $performanceLabel)
                                            ));
        }
        return $this->addXToTarget($target, array('value'));
    }

    public function addXToTarget($target, array $types)
    {
        foreach ($types as $type) {
            array_push($target['select'], $this->createSelect($type));
        }
        return $target;
    }

    private function createSelect($name)
    {
        return array(
                    array('type' => 'field', 'params' => array($name)),
                    array('type' => 'last', 'params' => array()),
                    );
    }
}
