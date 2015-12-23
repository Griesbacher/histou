<?php
/**
Contains Helper Class.
PHP version 5
@category Folder_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\helper;

/**
Helper Class.
PHP version 5
@category Helper_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

class Str
{
    /**
    Tests if a string ends with a given string
    @param string $stringToSearch string to search in.
    @param string $extension      string to search for.
    @return object.
    **/
    public static function endsWith($stringToSearch, $extension)
    {
        return $extension === "" ||
        (
        ($temp = strlen($stringToSearch) - strlen($extension)) >= 0
        && strpos($stringToSearch, $extension, $temp) !== false);
    }

    /**
    Returns true if $stringToSearch begins with $prefix.
    @param string $stringToSearch string to search within.
    @param string $prefix         string to search for.
    @return bool.
    **/
    public static function startsWith($stringToSearch, $prefix)
    {
        return $prefix === "" || strrpos($stringToSearch, $prefix, -strlen($stringToSearch)) !== false;
    }

    /**
    Returns an influxdb tabelname.
    @param string $host hostname.
    @param string $service servicename.
    @param string $command commandname.
    @param string $perfLabel perfLabelname.
    @param string $type perfLabeltype.
    @return string.
    **/
    public static function influxdbTablename($host, $service, $command, $perfLabel, $type)
    {
        return sprintf(
            '%s%s%s%s%s%s%s%s%s',
            $host,
            INFLUX_FIELDSEPERATOR,
            $service,
            INFLUX_FIELDSEPERATOR,
            $command,
            INFLUX_FIELDSEPERATOR,
            $perfLabel,
            INFLUX_FIELDSEPERATOR,
            $type
        );
    }
}
