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

    public function createTarget(array $filterTags = array(), $datasource = INFLUXDB_DB)
    {
        return array(
                    'measurement' => 'metrics',
                    'alias' => '$col',
                    'select' => array(),
                    'tags' => $this->createFilterTags($filterTags),
                    'dsType' => 'influxdb',
                    'resultFormat' => 'time_series',
                    'datasource' => $datasource
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

    public function addXToTarget($target, array $types, $alias, $color, $keepAlias = false, $createSelect = null)
    {
        if ($createSelect == null) {
            $createSelect = "\histou\grafana\graphpanel\GraphPanelInfluxdb::createSelect";
        }
        foreach ($types as $type) {
            if ($keepAlias) {
                $newalias = $alias;
            } else {
                $newalias = $alias.'-'.$type;
            }
            array_push($target['select'], call_user_func_array($createSelect, array($type, \histou\helper\str::escapeBackslash($newalias))));
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
