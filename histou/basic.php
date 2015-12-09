<?php
/**
Contains Basic Stuff.
PHP version 5
@category Basic_Stuff
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou;

/**
Basic Class.
PHP version 5
@category Basic_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class Basic
{
    /**
    Parses the configuration file.
    @param string $filename Path to the configuration file.
    @return null.
    **/
    public static function parsIni($filename)
    {
        if (empty($filename) || !file_exists($filename)) {
            return "Configuration not found";
        }

        $config = parse_ini_file($filename, true);
        Basic::setConstant(
            "DEFAULT_SOCKET_TIMEOUT",
            Basic::getConfigKey($config, 'general', 'socketTimeout'),
            10
        );
        Basic::setConstant(
            "PHP_COMMAND",
            Basic::getConfigKey($config, 'general', 'phpCommand'),
            "php"
        );
        Basic::setConstant(
            "TMP_FOLDER",
            Basic::getConfigKey($config, 'general', 'tmpFolder'),
            sys_get_temp_dir()
        );
        Basic::setConstant(
            "INFLUX_URL",
            Basic::getConfigKey($config, 'influxdb', 'influxdbUrl'),
            "http://127.0.0.1:8086/query?db=nagflux"
        );
		if (preg_match(";db=(\\w*);", INFLUX_URL, $matches)) {
			define('INFLUX_DB', $matches[1]);
		}

        Basic::setConstant(
            "INFLUX_FIELDSEPERATOR",
            Basic::getConfigKey($config, 'influxdb', 'influxFieldseperator'),
            "&"
        );
        Basic::setConstant(
            "DEFAULT_TEMPLATE_FOLDER",
            Basic::getConfigKey($config, 'folder', 'defaultTemplateFolder'),
            "histou/templates/default/"
        );
        Basic::setConstant(
            "CUSTOM_TEMPLATE_FOLDER",
            Basic::getConfigKey($config, 'folder', 'customTemplateFolder'),
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
    private static function getConfigKey(array $config, $level, $key)
    {
        if (array_key_exists($level, $config) && array_key_exists($key, $config[$level])) {
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
    private static function setConstant($NAME, $value, $alternative)
    {
        if (empty($value)) {
            define($NAME, $alternative);
        } else {
            define($NAME, $value);
        }
    }
}
