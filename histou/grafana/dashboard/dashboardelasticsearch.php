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
class DashboardElasticsearch extends Dashboard
{

    /**
    Constructs a new Dashboard.
    @param string $title name of the dashboard.
    @return null
    **/
    public function __construct($title, $sharedCrosshair)
    {
        parent::__construct($title,$sharedCrosshair);
    }

    public function addAnnotation($name, $query, $title, $text, $tags, $iconColor = '#751975', $lineColor = '#751975', $datasource = ELASTICSEARCH_INDEX, $enabled = SHOW_ANNOTATION, $iconSize = 13)
    {
        /*array_push(
            $this->data['annotations']['list'],
            array(
            "datasource" => ELASTICSEARCH_INDEX,
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
        );*/
    }

    /**
    Adds all default Annotations.
    @param string $hostname    hostname
    @param string $servicename servicename
    @return null
    **/
    public function addDefaultAnnotations($hostname, $servicename)
    {
        /*$annotations = array(
        array('host_notification', '#751975'),
        array('service_notification', '#198c19'),
        array('comment', '#008080'),
        array('acknowledgement', '#ff64d0'),
        array('downtime', '#A218E8'),
        );
        foreach ($annotations as $annotation) {
            $this->addAnnotation($annotation[0],
			"SELECT * FROM messages WHERE type = '$name' AND host = '$hostname' AND service = '$servicename' AND \$timeFilter",
			"type",
			"message",
			"author",
			$annotation[1], $annotation[1]);
        }*/
    }

    public function addTemplateForPerformanceLabel($name, $host, $service, $regex = '', $multiFormat = false, $includeAll = false)
    {
        $query = sprintf('{"find": "terms", "field": "performanceLabel", "query": "host: %s, service: %s"}', $host, $service);
        $this->addTemplate(ELASTICSEARCH_INDEX, $name, $query, $regex, $multiFormat, $includeAll);
    }

    /**
    https://github.com/grafana/grafana/issues/4075
    **/
    public function genTemplateVariable($variable)
    {
        return "\$$variable";
    }
}
