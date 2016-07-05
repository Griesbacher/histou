<?php
/**
Contains Dashboard Class.
PHP version 5
@category Dashboard_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\grafana\dashboard;

/**
Dashboard Class.
PHP version 5
@category Dashboard_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
abstract class Dashboard
{
    protected $data = array(
        'id' => '1',
        'title' => null,
        'originalTitle' => 'CustomDashboard',
        'tags' => array(),
        'timezone' => 'browser',
        'editable' => true,
        'hideControls' => true,
        'sharedCrosshair' => false,
        'nav' => array(array(
            'type' => 'timepicker',
            'enable' => true,
            'time_options' => array(
                "5m","15m","1h","6h","12h","24h","2d","7d","30d"
            ),
            'refresh_intervals' => array(
                "5s","10s","30s","1m","5m","15m","30m","1h","2h","1d"
            ),
            'now' => true,
            'collapse' => false,
            'notice' => false,
            )),
        'time' => array(
            'from' => 'now-8h',
            'to' => 'now',
        ),
        'templating' => array(),
        'annotations' => array(),
        'refresh' => '30s',
        'version' => "6",
        'rows' => array(),
        'annotations' => array('enable' => true, 'list' => array()),
        'templating' => array('list' => array()),
    );
    protected $rows = array();
    public static $forecast = array();

    /**
    Constructs a new Dashboard.
    @param string $title name of the dashboard.
    @return null
    **/
    public function __construct($title)
    {
        $this->data['title'] = $title;
    }

    /**
    Creates a array, with all sub elements.
    @return array
    **/
    public function toArray()
    {
        foreach ($this->rows as $row) {
            array_push($this->data['rows'], $row->toArray());
        }
        if (!empty(Dashboard::$forecast)) {
            $this->data['time']['to'] = 'now+'.\histou\helper\CustomTime::getLongestTime(Dashboard::$forecast);
        }
        return $this->data;
    }

    /**
    Setter for Editable.
    @param boolean $editable true7false.
    @return null
    **/
    public function setEditable($editable)
    {
        $this->data['editable'] = $editable;
    }

    /**
    Setter Everything.
    @param string $name  key.
    @param string $value value.
    @return null
    **/
    public function setCustomProperty($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
    Adds a row to the dashboard.
    @param Row $row new Row.
    @return null
    **/
    public function addRow(\histou\grafana\Row $row)
    {
        array_push($this->rows, $row);
    }

    public function addTemplate($datasource, $name, $query, $regex, $multiFormat, $includeAll)
    {
        $this->data['templating']['enable'] = true;
        array_push(
            $this->data['templating']['list'],
            array(
                'allFormat' => 'regex values',
                'datasource' => $datasource,
                'includeAll' => $includeAll,
                'multi' => $multiFormat,
                'multiFormat' => 'regex values',
                'name' => $name,
                'query' => $query,
                'refresh' => 1,
                'regex' => $regex,
                'type' => 'query',
            )
        );
    }

    /**
    Adds a Annotation to the dashboard.
    @param string $name        name to display.
    @param string $hostname    hostname to search for.
    @param string $servicename servicename to search for.
    @param string $iconColor   Color of the arrow.
    @param string $lineColor   Color of the vertical line.
    @param bool   $enabled     Is Annotation by default enabled.
    @param int    $iconSize    Size of the arrow.
    @param string $datasource  name of the grafana datasource.
    @return return null
    **/
    abstract public function addAnnotation($name, $hostname, $servicename, $iconColor, $lineColor, $enabled = SHOW_ANNOTATION, $iconSize = 13);

    /**
    Adds all default Annotations.
    @param string $hostname    hostname
    @param string $servicename servicename
    @return null
    **/
    abstract public function addDefaultAnnotations($hostname, $servicename);

    abstract public function addTemplateForPerformanceLabel($name, $host, $service, $regex = '', $multiFormat = false, $includeAll = false);

    abstract public function genTemplateVariable($variable);
}
