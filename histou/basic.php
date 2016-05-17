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
    public static $parsed = false;
    /**
    Parses the GET parameter.
    @return null.
    **/
    public static function parsArgs()
    {
        if (static::$parsed) {
            return;
        }
        static::$parsed = true;

        $shortopts  = "";
        $longopts  = array(
        "host:",
        "service:",
        );

        $args = getopt($shortopts, $longopts);
        if (isset($_GET['host']) && !empty($_GET['host'])) {
            define("HOST", $_GET["host"]);
        } elseif (isset($args['host']) && !empty($args['host'])) {
            define("HOST", $args["host"]); // @codeCoverageIgnore
        } else {  // @codeCoverageIgnore
            \histou\Basic::returnData('Hostname is missing!', 1, 'Hostname is missing!');
        }

        if (isset($_GET['service']) && !empty($_GET['service'])) {
            define("SERVICE", $_GET["service"]);
        } elseif (isset($args['service']) && !empty($args['service'])) {
            define("SERVICE", $args["service"]);  // @codeCoverageIgnore
        } else {   // @codeCoverageIgnore
            define("SERVICE", HOSTCHECK_ALIAS);
        }

        if (isset($_GET['debug'])) {
            \histou\Debug::enable();
        }

        if (isset($_GET['height']) && !empty($_GET['height'])) {
            define("HEIGHT", $_GET["height"]);
        } else {
            define("HEIGHT", "400px");
        }

        if (isset($_GET['legend']) && !empty($_GET['legend']) && $_GET["legend"] == "false") {
            define("SHOW_LEGEND", false);
        } else {
            define("SHOW_LEGEND", true);
        }

        if (isset($_GET['annotations']) && !empty($_GET['annotations']) && $_GET["annotations"] == "true") {
            define("SHOW_ANNOTATION", true);
        } else {
            define("SHOW_ANNOTATION", false);
        }
    }

    /**
    This function will print its input and exit with the given returncode.
    @param object $data       This object will be converted to json.
    @param int    $returnCode The returncode the programm will exit.
    @return null.
    **/
    public static function returnData($data, $returnCode = 0)
    {
        if (is_object($data) && is_subclass_of($data, 'histou\grafana\dashboard\Dashboard')) {
            if (\histou\Debug::isEnable()) {
                //$data->addRow(\histou\Debug::genMarkdownRow(\histou\Debug::getLogAsMarkdown(), 'Debug')); //for markdown
                $data->addRow(\histou\Debug::genRow(\histou\Debug::getLog()));
            }
            $data = $data->toArray();
            $json = json_encode($data);
        } elseif (is_string($data)) {
            $json = $data;
        } else {
            echo '<pre>';
            print_r("Don't know what to do with this: $data");
            echo '</pre>';
        }
        if (isset($json)) {
            if (isset($_GET["callback"]) && !empty($_GET["callback"])) {
                header('content-type: application/json; charset=utf-8');
                echo "{$_GET['callback']}($json)";
            } else {
                echo "<pre>";
                print_r($data);
                echo "<br>";
                print_r($returnCode);
                echo "<br>";
                print_r($json);
                echo "<br>";
                echo "</pre>";
            }
        }
    }

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
            "SPECIAL_CHAR",
            Basic::getConfigKey($config, 'general', 'specialChar'),
            "&"
        );
        Basic::setConstant(
            "DATABASE_TYPE",
            strtolower(Basic::getConfigKey($config, 'general', 'databaseType')),
            "influxdb"
        );
        Basic::setConstant(
            "FORECAST_DATASOURCE_NAME",
            strtolower(Basic::getConfigKey($config, 'general', 'forecastDatasourceName')),
            "forecast"
        );
        Basic::setConstant(
            "URL",
            Basic::getConfigKey($config, DATABASE_TYPE, 'url'),
            "http://127.0.0.1:8086/query?db=nagflux"
        );
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
        if (DATABASE_TYPE == INFLUXDB && preg_match(";db=(\\w*);", URL, $matches)) {
            define('INFLUXDB_DB', $matches[1]);
        } elseif (DATABASE_TYPE == ELASTICSEARCH) {
            $path = parse_url(URL, PHP_URL_PATH);
            if ($path) {
                define('ELASTICSEARCH_INDEX', ltrim($path, '/'));
            }
        }
        Basic::setConstant(
            "HOSTCHECK_ALIAS",
            Basic::getConfigKey($config, DATABASE_TYPE, 'hostcheckAlias'),
            "hostcheck"
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
        Basic::setConstant(
            "FORECAST_TEMPLATE_FOLDER",
            Basic::getConfigKey($config, 'folder', 'forecastTemplateFolder'),
            "histou/forecasts/"
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
