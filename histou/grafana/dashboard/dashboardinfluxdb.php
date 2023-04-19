<?php
/**
Contains Dashboard Class.
PHP version 5
@category Dashboard_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
namespace histou\grafana\dashboard;

/**
Dashboard Class.
PHP version 5
@category Dashboard_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
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

    public function addAnnotation($name, $query, $title, $text, $tags, $iconColor = '#751975', $lineColor = '#751975', $datasource = INFLUXDB_DB, $enabled = SHOW_ANNOTATION, $iconSize = 13)
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
            "query" => $query,
            "showLine" => true,
            "tagsColumn" => $tags,
            "textColumn" => $text,
            "titleColumn" => $title
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
            $this->addAnnotation(
                $annotation[0],
                "SELECT * FROM messages WHERE type = '$annotation[0]' AND host = '$hostname' AND service = '$servicename' AND \$timeFilter",
                "type",
                "message",
                "author",
                $annotation[1],
                $annotation[1]
            );
        }
    }

    public function addTemplateForPerformanceLabel($name, $host, $service, $regex = '', $multiFormat = false, $includeAll = false)
    {
        $query = sprintf('SHOW TAG VALUES WITH KEY = "performanceLabel" WHERE "host" = \'%s\' AND "service" = \'%s\'', $host, $service);
        $this->addTemplate(INFLUXDB_DB, $name, $query, $regex, $multiFormat, $includeAll);
    }

    public function genTemplateVariable($variable)
    {
        return "[[$variable]]";
    }
}
