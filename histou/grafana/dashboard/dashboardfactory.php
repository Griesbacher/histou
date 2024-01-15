<?php
/**
Contains a dashboard factory.
PHP version 5
@category Dashboard_Factory
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
namespace histou\grafana\dashboard;

/**
dashboard factory.
PHP version 5
@category Dashboard_Factory
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
class DashboardFactory
{
    /**
    Returns depending on the Database_Type a dashboard.
    @returns a dashboard
    **/
    public static function generateDashboard($title)
    {
        if (DATABASE_TYPE == INFLUXDB) {
            return new \histou\grafana\dashboard\DashboardInfluxDB($title);
        } elseif (DATABASE_TYPE == VICTORIAMETRICS) {
            return new \histou\grafana\dashboard\DashboardVictoriametrics($title);
        } else {
             throw new \InvalidArgumentException("The given Database is unkown:".DATABASE_TYPE);
        }
    }
}
