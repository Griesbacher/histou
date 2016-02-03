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
namespace histou\grafana;

/**
Dashboard Class.
PHP version 5
@category Dashboard_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class Dashboard
{
    private $data = array(
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
            'status' => 'Stable',
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
        'annotations' => array('list' => array()),
        'templating' => array('list' => array()),
    );
    private $rows = array();

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
    public function addRow(Row $row)
    {
        array_push($this->rows, $row);
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
    public function addAnnotation($name, $hostname, $servicename, $iconColor, $lineColor, $enabled = SHOW_ANNOTATION, $iconSize = 13, $datasource = INFLUX_DB)
    {
        array_push(
            $this->data['annotations']['list'],
            array(
            "datasource" => $datasource,
            "enable" => $enabled,
            "iconColor" => $iconColor,
            "iconSize" => $iconSize,
            "lineColor" => $lineColor,
            "name" => $name,
            "query" => "SELECT * FROM messages WHERE type = '$name' AND host = '$hostname' AND service = '$servicename' AND \$timeFilter",
            "showLine" => true,
            "tagsColumn" => "author",
            "textColumn" => "value",
            "titleColumn" => "type"
            )
        );
    }

    /**
    Adds all default Annotations.
    @param string $hostname    hostname
    @param string $servicename servicename
    @return null
    **/
    public function addDefaultAnnotations($hostname, $servicename)
    {
        $annotations = array(
        array('host_notification', '#751975'),
        array('service_notification', '#198c19'),
        array('comment', '#008080'),
        array('acknowledgement', '#ff64d0'),
        array('downtime', '#A218E8'),
        );
        foreach ($annotations as $annotation) {
            $this->addAnnotation($annotation[0], $hostname, $servicename, $annotation[1], $annotation[1]);
        }
    }

    public function addTemplate($name, $query, $regex = '', $multiFormat = false, $includeAll = false, $datasource = INFLUX_DB)
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
                'refresh' => true,
                'refresh_on_load' => false,
                'regex' => $regex,
                'type' => 'query',
            )
        );
    }
}
