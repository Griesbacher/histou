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
namespace histou\grafana\singlestatpanel;

/**
Base Panel.
PHP version 5
@category Panel_Factory
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class SinglestatPanelFactory
{
    /**
    Returns depending on the Database_Type a singlestatPanel.
    @returns a singlestatPanel
    **/
    public static function generatePanel($title, $id = -1)
    {
        if (DATABASE_TYPE == INFLUXDB) {
            return new \histou\grafana\singlestatpanel\SinglestatPanelInfluxdb($title, $id);
        } elseif (DATABASE_TYPE == ELASTICSEARCH) {
            throw new \InvalidArgumentException(DATABASE_TYPE. "is currently not supported");
        } else {
            throw new \InvalidArgumentException("The given Database is unkown:".DATABASE_TYPE);
        }
    }
}
