<?php
define("DEFAULT_TEMPLATE_FOLDER", "histou/templates/default/");
define("CUSTOM_TEMPLATE_FOLDER", "histou/templates/custom/");

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
    setConstant("DEFAULT_SOCKET_TIMEOUT", $config['general']['socketTimeout'], $config['default']['socketTimeout']);
    setConstant("INFLUX_URL", $config['influxdb']['influxdbUrl'], $config['default']['influxdbUrl']);
    setConstant("INFLUX_FIELDSEPERATOR", $config['influxdb']['influxFieldseperator'], $config['default']['influxFieldseperator']);
}
/**
Creates constatans with the value of $value if it is empty the $alternative is taken.
@param string $name        Name of the constant.
@param object $value       Value of the constant.
@param object $alternative Alternative value of the constant.
@return null.
**/
function setConstant($name, $value, $alternative)
{
    if (empty($value)) {
        define($name, $alternative);
    } else {
        define($name, $value);
    }
}
?>