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
class DashboardInfluxDB extends Dashboard
{
    /**
    Constructs a new Dashboard.
    @param string $title name of the dashboard.
    @return null
    **/
    public function __construct($title)
    {
        parent::__construct($title);
    }

    public function addAnnotation($name, $hostname, $servicename, $iconColor, $lineColor, $enabled = SHOW_ANNOTATION, $iconSize = 13)
    {
        array_push(
            $this->data['annotations']['list'],
            array(
            "datasource" => INFLUXDB_DB,
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

    public function addTemplateForPerformanceLabel($name, $host, $service, $regex = '', $multiFormat = false, $includeAll = false)
    {
        $query = sprintf('SHOW TAG VALUES WITH KEY = "performanceLabel" WHERE "host" = \'%s\' AND "service" = \'%s\'}', $host, $name);
        $this->addTemplate(ELASTICSEARCH_INDEX, $name, $query, $regex, $multiFormat = false, $includeAll = false);
    }

    public function genTemplateVariable($variable)
    {
        return "[[$variable]]";
    }
}
