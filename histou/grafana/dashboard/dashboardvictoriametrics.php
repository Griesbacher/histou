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
class DashboardVictoriametrics extends Dashboard
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

    public function addAnnotation($name, $query, $title, $text, $tags, $iconColor = '#751975', $lineColor = '#751975', $datasource = VICTORIAMETRICS_DS, $enabled = SHOW_ANNOTATION, $iconSize = 13)
    {
    }

    /**
    Adds all default Annotations.
    @param string $hostname    hostname
    @param string $servicename servicename
    @return null
    **/
    public function addDefaultAnnotations($hostname, $servicename)
    {
    }

    public function addTemplateForPerformanceLabel($name, $host, $service, $regex = '', $multiFormat = false, $includeAll = false)
    {
        $query = sprintf('{"find": "terms", "field": "performanceLabel", "query": "host: %s, service: %s"}', $host, $service);
        $this->addTemplate(VICTORIAMETRICS_DS, $name, $query, $regex, $multiFormat, $includeAll);
    }

    /**
    https://github.com/grafana/grafana/issues/4075
    **/
    public function genTemplateVariable($variable)
    {
        return "\$$variable";
    }
}
