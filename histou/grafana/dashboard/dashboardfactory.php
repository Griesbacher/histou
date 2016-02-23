<?php
/**
Contains types of Panels.
PHP version 5
@category Panel_Factory
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\grafana\dashboard;

/**
Base Panel.
PHP version 5
@category Panel_Factory
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class DashboardFactory
{
    /**
    Returns depending on the Database_Type a graphpanel.
    @returns a graphPanel
    **/
    public static function generateDashboard($title)
    {
        if (DATABASE_TYPE == INFLUXDB) {
            return new \histou\grafana\dashboard\DashboardInfluxDB($title);
        } elseif (DATABASE_TYPE == ELASTICSEARCH) {
            return new \histou\grafana\dashboard\DashboardElasticsearch($title);
        }
    }
}
