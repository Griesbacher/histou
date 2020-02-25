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

/* label_replace({__name__=~"metrics_(value|crit)"}, "__tmp_alias", "$1", "__name__", "metrics_(.*)") */
/*
   {
      "datasource": "victoria",
      "refId": "C",
      "expr": "{fooo=\"bar\"}",
      "legendFormat": "asdf"
    }
*/

class Target extends \ArrayObject implements \JsonSerializable {
    public function jsonSerialize() {
        $r = array(
            'datasource' => $this['datasource'],
            'legendFormat' => $this['legendFormat'],
            'expr' => $this->getExpr()
        );
        return $r;

    }

    private function getExpr() {
        $expr =  '{__name__=~"' . $this['measurement'] . "_(" . $this->getSelect() . ')",' . $this->getFilter() . '}';
        return 'label_replace(' . $expr . ', "__tmp_alias", "$1", "__name__", "metrics_(.*)")';
    }

    private function getSelect() {
       return join("|", array_map(function($x){
           return $x[0];
       }, $this['select']));
    }

    private function getFilter() {
        $filter = array();
        foreach($this['tags'] as $key => $val) {
            array_push($filter, $key . '="' . $val['value'] . '"');
        }
        return join(",", $filter);
    }
}

/**
Base Panel.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class GraphPanelVictoriametrics extends GraphPanel
{
    /**
    Constructor.
    @param string  $title      name of the panel.
    @param boolean $legendShow hide the legend or not
    @return object.
    **/
    public function __construct($title, $legendShow = SHOW_LEGEND, $id = -1)
    {
        parent::__construct($title, $legendShow, $id);
    }

    public function createTarget(array $filterTags = array(), $datasource = VICTORIAMETRICS_DS)
    {
        return new Target(array(
                    'measurement' => 'metrics',
                    'legendFormat' => '{{performanceLabel}}-{{__tmp_alias}}',
                    'select' => array(),
                    'tags' => $this->createFilterTags($filterTags),
                    'dsType' => 'prometheus',
                    'resultFormat' => 'time_series',
                    'datasource' => $datasource,
                    ));
    }
    
    /**
    Creates filter tags array based on host, service...
    **/
    private function createFilterTags(array $filterTags = array())
    {
        return $filterTags;
        $tags = array();
        $i = 0;
        foreach ($filterTags as $key => $value) {
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
    public function genTargetSimple($host, $service, $command, $performanceLabel, $color = '#085DFF', $alias = '', $useRegex = false)
    {
        return $this->genTarget($host, $service, $command, $performanceLabel, $color, $alias, $useRegex);
    }

    /**
    This creates a target with an value.
    **/
    public function genTarget($host, $service, $command, $performanceLabel, $color = '#085DFF', $alias = '', $useRegex = false, $customSelect = null)
    {
        if ($alias == '') {
            $alias = $performanceLabel;
        }
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
        return $this->addXToTarget($target, array('value'), $alias, $color, false, $customSelect);
    }

    public function addWarnToTarget($target, $alias = '', $color = true, $keepAlias = false)
    {
        if ($color) {
            return $this->addXToTarget($target, array('warn', 'warn-min', 'warn-max'), $alias, '#FFFC15', $keepAlias);
        }
        return $this->addXToTarget($target, array('warn', 'warn-min', 'warn-max'), $alias, '', $keepAlias);
    }

    public function addCritToTarget($target, $alias = '', $color = true, $keepAlias = false)
    {
        if ($color) {
            return $this->addXToTarget($target, array('crit', 'crit-min', 'crit-max'), $alias, '#FF3727', $keepAlias);
        }
        return $this->addXToTarget($target, array('crit', 'crit-min', 'crit-max'), $alias, '', $keepAlias);
    }

    public function addXToTarget($target, array $types, $alias, $color, $keepAlias = false, $createSelect = null)
    {
        //if ($createSelect == null) {
            //$createSelect = "\histou\grafana\graphpanel\GraphPanelInfluxdb::createSelect";
        //}
        foreach ($types as $type) {
            if ($keepAlias) {
                $newalias = $alias;
            } else {
                $newalias = $alias.'-'.$type;
            }
            array_push($target['select'], array($type, $newalias));
            //array_push($target['select'], "[$type x $newalias]");
            if ($color != '') {
                $this->addAliasColor($newalias, $color);
            }
        }
        return $target;
    }

    public static function createCounterSelect($name, $alias)
    {
        return array(
                    array('type' => 'field', 'params' => array($name)),
                    array('type' => 'mean', 'params' => array()),
                    array('type' => 'difference', 'params' => array()),
                    array('type' => 'alias', 'params' => array($alias))
                    );
    }

    public static function createSelect($name, $alias)
    {
        return array($name, $alias);
        return array(
                    array('type' => 'field', 'params' => array($name)),
                    array('type' => 'mean', 'params' => array()),
                    array('type' => 'alias', 'params' => array($alias))
                    );
    }

    /**
    This creates a target for an downtime.
    **/
    public function genDowntimeTarget($host, $service, $command, $performanceLabel, $alias = '', $useRegex = false, $customSelect = null)
    {
        if ($alias == '') {
            $alias = 'downtime';
        }
        if ($useRegex) {
            $target = $this->createTarget(
                array(
                        'host' => array('value' => \histou\helper\str::genRegex($host), 'operator' => '=~'),
                        'service' => array('value' => \histou\helper\str::genRegex($service), 'operator' => '=~'),
                        'command' => array('value' => \histou\helper\str::genRegex($command), 'operator' => '=~'),
                        'performanceLabel' => array('value' => \histou\helper\str::genRegex($performanceLabel), 'operator' => '=~'),
                        'downtime' => array('value' => "true")
                    )
            );
        } else {
            $target = $this->createTarget(
                array(
                        'host' => array('value' => $host),
                        'service' => array('value' => $service),
                        'command' => array('value' => $command),
                        'performanceLabel' => array('value' => $performanceLabel),
                        'downtime' => array('value' => "true")
                    )
            );
        }
        $target = $this->addXToTarget($target, array('value'), $alias, '#EEE', true, $customSelect);
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
    
    /**
    This creates a target for an forecast.
    @return Returns a target if a forcast config exists, null otherwise.
    **/
    public function genForecastTarget($host, $service, $command, $performanceLabel, $color = '#000', $alias = '', $useRegex = false, $addMethodToName = false)
    {
        $forecastConfig = \histou\template\ForecastTemplate::$config;
        if (!$forecastConfig || !array_key_exists($performanceLabel, $forecastConfig)) {
            return null;
        }
        array_push(\histou\grafana\dashboard\Dashboard::$forecast, $forecastConfig[$performanceLabel]['forecast']);
        if ($alias == '') {
            $alias = $performanceLabel.'-forecast';
        }
        if ($addMethodToName) {
            $alias .= '-'.$forecastConfig[$performanceLabel]['method'];
        }
        if ($useRegex) {
            $target = $this->createTarget(
                array(
                        'host' => array('value' => \histou\helper\str::genRegex($host), 'operator' => '=~'),
                        'service' => array('value' => \histou\helper\str::genRegex($service), 'operator' => '=~'),
                        //'command' => array('value' => \histou\helper\str::genRegex($command), 'operator' => '=~'),
                        'performanceLabel' => array('value' => \histou\helper\str::genRegex($performanceLabel), 'operator' => '=~'),
                    ),
                FORECAST_DATASOURCE_NAME
            );
        } else {
            $target = $this->createTarget(
                array(
                        'host' => array('value' => $host),
                        'service' => array('value' => $service),
                        //'command' => array('value' => $command),
                        'performanceLabel' => array('value' => $performanceLabel),
                    ),
                FORECAST_DATASOURCE_NAME
            );
        }
        $target = $this->addXToTarget($target, array('value'), $alias, $color, true);
        $this->addToSeriesOverrides(
            array(
                'alias' => $alias,
                'legend' => false,
                'lines' => false,
                'points' => true,
                'pointradius' => 1,
            )
        );
        return $target;
    }
}
