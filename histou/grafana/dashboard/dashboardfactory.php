<?php
/**
Contains a dashboard factory.
PHP version 5
@category Dashboard_Factory
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\grafana\dashboard;

/**
dashboard factory.
PHP version 5
@category Dashboard_Factory
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class DashboardFactory
{
    /**
    Returns depending on the Database_Type a dashboard.
    @returns a dashboard
    **/
    public static function generateDashboard($title, $sharedCrosshair=false)
    {
        if (DATABASE_TYPE == INFLUXDB) {
            return new \histou\grafana\dashboard\DashboardInfluxDB($title, $sharedCrosshair);
        } elseif (DATABASE_TYPE == ELASTICSEARCH) {
            return new \histou\grafana\dashboard\DashboardElasticsearch($title, $sharedCrosshair);
        } else {
             throw new \InvalidArgumentException("The given Database is unkown:".DATABASE_TYPE);
        }
    }    
}
