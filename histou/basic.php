<?php
/**
Contains Basic Stuff.
PHP version 5
@category Basic_Stuff
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

/**
Parses the configuration file.
@param string $filename Path to the configuration file.
@return null.
**/
function parsIni($filename)
{
    if (empty($filename) || !file_exists($filename)) {
        returnData("", 1, "Configuration not found: ".$filename);
    }

    $config = parse_ini_file($filename, true);
    setConstant(
        "DEFAULT_SOCKET_TIMEOUT",
        getConfigKey($config, 'general', 'socketTimeout'),
        10
    );
    setConstant(
        "PHP_COMMAND",
        getConfigKey($config, 'general', 'phpCommand'),
        "php"
    );
    setConstant(
        "INFLUX_URL",
        getConfigKey($config, 'influxdb', 'influxdbUrl'),
        "http://127.0.0.1:8086/query?db=icinga"
    );
    setConstant(
        "INFLUX_FIELDSEPERATOR",
        getConfigKey($config, 'influxdb', 'influxFieldseperator'),
        "&"
    );
    setConstant(
        "DEFAULT_TEMPLATE_FOLDER",
        getConfigKey($config, 'folder', 'defaultTemplateFolder'),
        "histou/templates/default/"
    );
    setConstant(
        "CUSTOM_TEMPLATE_FOLDER",
        getConfigKey($config, 'folder', 'customTemplateFolder'),
        "histou/templates/custom/"
    );
}

/**
Checks Config for entries and returns them if found.
@param array  $config config array.
@param string $level  config level.
@param string $key    config key.
@return string or null.
**/
function getConfigKey(array $config, $level, $key)
{
    if (array_key_exists($level, $config) && array_key_exists($key, $config[$level]) {
        return $config[$level][$key];
    }
    return null;
}

/**
Creates constatans with $value if it is empty the $alternative is taken.
@param string $NAME        Name of the constant.
@param object $value       Value of the constant.
@param object $alternative Alternative value of the constant.
@return null.
**/
function setConstant($NAME, $value, $alternative)
{
    if (empty($value)) {
        define($NAME, $alternative);
    } else {
        define($NAME, $value);
    }
}
