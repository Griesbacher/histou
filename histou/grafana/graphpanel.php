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
namespace histou\grafana;

/**
Base Panel.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class GraphPanel extends Panel
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
        $this->data['tooltip'] = array(
                                'show' =>  false,
                                'values' =>  false,
                                'min' =>  false,
                                'max' =>  false,
                                'current' =>  false,
                                'total' =>  false,
                                'avg' =>  false,
                                'shared' =>  true
                            );
        $this->data['legend'] = array(
                                "show" => $legendShow,
                                "values" => false,
                                "min" => false,
                                "max" => false,
                                "current" => false,
                                "total" => false,
                                "avg" => false,
                                "alignAsTable" => false,
                                "rightSide" => false,
                                "hideEmpty" => true
                            );
        $this->data['fill'] = 0;
        $this->data['linewidth'] = 2;
        $this->data['targets'] = array();
        $this->data['seriesOverrides'] = array();
        $this->data['datasource'] = "-- Mixed --";
    }

    /**
    Setter for setTooltip
    @param int $tooltip setTooltip.
    @return null.
    **/
    public function setTooltip(array $tooltip)
    {
        $this->data['tooltip'] = $tooltip;
    }
    /**
    Adds an array to the seriesOverrides field and checks for leading slashes.
    **/
    public function addToSeriesOverrides(array $data)
    {
        if (\histou\helper\str::isRegex($data['alias'])) {
            $data['alias'] = '/'.str_replace('/', '\/', $data['alias']).'/';
        }
        array_push($this->data['seriesOverrides'], $data);
    }

    /**
    Changes the color of a line.
    @param string $alias linename.
    @param string $color hexcolor.
    @return null.
    **/
    public function addAliasColor($alias, $color)
    {
        if (!isset($this->data['aliasColors'])) {
            $this->data['aliasColors'] = array();
        }
        $this->data['aliasColors'][$alias] = $color;
    }

    /**
    Changes the color of a line by regex.
    @param string $regex linename.
    @param string $color hexcolor.
    @return null.
    **/
    public function addRegexColor($regex, $color)
    {
        $this->addToSeriesOverrides(
            array(
                'alias' => $regex,
                'color' => $color,
            )
        );
    }
    /**
    Setter for leftYAxisLabel
    @param string $label label.
    @return null.
    **/
    public function setLeftYAxisLabel($label)
    {
        $this->data['leftYAxisLabel'] = $label;
    }

    /**
    Setter for rightYAxisLabel
    @param string $label label.
    @return null.
    **/
    public function setRightYAxisLabel($label)
    {
        $this->data['rightYAxisLabel'] = $label;
    }

    /**
    Tries to convert the given unit in a "grafana unit" if not possible the leftYAxisLabel will be set.
    @param string $unit unit.
    @return null.
    **/
    public function setLeftUnit($unit)
    {
        $gUnit = $this->convertUnit($unit);
        if (array_key_exists('y_formats', $this->data) && sizeof($this->data['y_formats']) > 0) {
            $this->data['y_formats'][0] = $gUnit;
        } else {
            $this->data['y_formats'] = array($gUnit, 'short');
        }
        if ($gUnit == 'short') {
            $this->setLeftYAxisLabel($unit);
        }
    }

    /**
    Tries to convert the given unit in a "grafana unit" if not possible the rightYAxisLabel will be set.
    @param string $unit unit.
    @return null.
    **/
    public function setRightUnit($unit)
    {
        $gUnit = $this->convertUnit($unit);
        if (array_key_exists('y_formats', $this->data) && sizeof($this->data['y_formats']) > 0) {
            $this->data['y_formats'][1] = $gUnit;
        } else {
            $this->data['y_formats'] = array('short', $gUnit);
        }
        if ($gUnit == 'short') {
            $this->setRightYAxisLabel($unit);
        }
    }

    /**
    Try to convert the given unit in a grafana unit.
    @param string $label unit.
    @return string if found a grafanaunit.
    **/
    private function convertUnit($unit)
    {
        switch ($unit) {
            //Time
            case 'ns':
            case 'µs':
            case 'ms':
            case 's':
            case 'm':
            case 'h':
            case 'd':
                return $unit;
            //Data
            case 'b':
                return 'bits';
                break;
            case 'B':
                return 'bytes';
            case 'KB':
            case 'KiB':
            case 'kiB':
            case 'kB':
                return 'kbytes';
            case 'MB':
            case 'MiB':
            case 'miB':
            case 'mB':
                return 'mbytes';
            case 'GB':
            case 'GiB':
            case 'giB':
            case 'gB':
                return 'gbytes';
            case '%':
            case 'percent':
            case 'pct':
            case 'pct.':
            case 'pc':
                return 'percent';
            default:
                return 'short';
        }
    }

    /**
    Setter for Linewidth
    @param int $width Linewidth.
    @return null.
    **/
    public function setLinewidth($width)
    {
        $this->data['linewidth'] = $width;
    }

    /**
    Fills the area below a line.
    @param string $alias     name of the query.
    @param int    $intensity intensity of the color.
    @return null.
    **/
    public function fillBelowLine($alias, $intensity)
    {
        $this->addToSeriesOverrides(
            array(
                'alias' => $alias,
                'fill' => $intensity,
            )
        );
    }
    /**
    Negates the Y Axis.
    @param string $alias     name of the query.
    @return null.
    **/
    public function negateY($alias)
    {
        $this->addToSeriesOverrides(
            array(
                'alias' => $alias,
                'transform' => 'negative-Y'
            )
        );
    }
    /**
    Display the values on the left or right y axis, left = 1 right = 2.
    @param string $alias name of the query.
    @return null.
    **/
    public function setYAxis($alias, $number = 1)
    {
        $this->addToSeriesOverrides(
            array(
                'alias' => $alias,
                'yaxis' => $number
            )
        );
    }

    public function createTarget(array $filterTags = array())
    {
        return array(
                    'measurement' => 'metrics',
                    'alias' => '$col',
                    'select' => array(),
                    'tags' => $this->createFilterTags($filterTags),
                    'dsType' => 'influxdb',
                    'resultFormat' => 'time_series',
                    'datasource' => INFLUX_DB
                    );
    }

    /**
    Creates filter tags array based on host, service...
    **/
    public function createFilterTags(array $filterTags = array())
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

    private function addXToTarget($target, $types, $alias, $color, $keepAlias = false)
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

    public function createSelect($name, $alias)
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

    /**
    Adds the target to the dashboard.
    **/
    public function addTarget($target)
    {
        array_push($this->data['targets'], $target);
    }
}
