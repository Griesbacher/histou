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
    public static $request = null;
    public static $phpCommand = "php";
    public static $height = "400px";
    public static $descriptorSpec = array(
        0 => array("pipe", "r"),    // STDIN
        1 => array("pipe", "w"),    // STDOUT
        2 => array("pipe", "w")     // STERR
    );
    public static $disablePanelTitle = false;
    public static $specificTemplate = '';
    public static $disablePerfdataLookup = false;

    public static $defaultInfluxdbGroupByTime = null;
 
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
        "command:",
        "perf_label:",
        "request:",
        );
        $args = getopt($shortopts, $longopts);
        
        $input = file_get_contents('php://input');
        if (!empty($input)) { // @codeCoverageIgnore
            static::$request = json_decode($input, true); // @codeCoverageIgnore
        } elseif (isset($args['request']) && !empty($args['request'])) { // @codeCoverageIgnore
            static::$request = json_decode($args['request'], true);// @codeCoverageIgnore
        }// @codeCoverageIgnore
        
        if (!static::$request) {
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
            
            if (isset($_GET['command']) && !empty($_GET['command'])) {
                define("COMMAND", $_GET["command"]);
            } elseif (isset($args['command']) && !empty($args['command'])) {
                define("COMMAND", $args["command"]);  // @codeCoverageIgnore
            }  // @codeCoverageIgnore
            
            if (isset($_GET['perf_label']) && !empty($_GET['perf_label'])) {
                global $PERF_LABEL;
                $PERF_LABEL = $_GET["perf_label"];
            } elseif (isset($args['perf_label']) && !empty($args['perf_label'])) {
                global $PERF_LABEL;
                $PERF_LABEL = $args["perf_label"];  // @codeCoverageIgnore
            }  // @codeCoverageIgnore
            if (isset($PERF_LABEL) && !is_array($PERF_LABEL)) {
                $PERF_LABEL = array($PERF_LABEL);
            }
        }

        if (isset($_GET['disablePanelTitle'])) {
            static::$disablePanelTitle = true;
        }

        if (isset($_GET['debug'])) {
            \histou\Debug::enable();
        }

        if (isset($_GET['height']) && !empty($_GET['height'])) {
            static::$height = $_GET["height"];
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

        if (isset($_GET['specificTemplate']) && !empty($_GET['specificTemplate'])) {
            static::$specificTemplate = $_GET["specificTemplate"];
        }

        if (static::$specificTemplate != "" && isset($_GET['disablePerfdataLookup'])) {
            static::$disablePerfdataLookup = true;
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
            print_r("Don't know what to do with this type: ".gettype($data));
            var_dump($data);
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
        $phpCommand = Basic::getConfigKey($config, 'general', 'phpCommand');
        if (!empty($phpCommand)) {
            static::$phpCommand = $phpCommand;
        }
        $disablePanelTitle = Basic::getConfigKey($config, 'general', 'disablePanelTitle');
        if (!empty($disablePanelTitle)) {
            static::$disablePanelTitle = $disablePanelTitle;
        }
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
            "nagflux_forecast"
        );

        $defaultInfluxdbGroupByTime = Basic::getConfigKey($config, 'graph', 'defaultInfluxdbGroupByTime');
        if (!empty($defaultInfluxdbGroupByTime)) {
            static::$defaultInfluxdbGroupByTime = $defaultInfluxdbGroupByTime;
        }

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
    
    public static function testConfig()
    {
        //test php command
        $cmd = static::$phpCommand." -h 2>&1";
        $process = proc_open($cmd, \histou\Basic::$descriptorSpec, $pipes);
        if (!is_resource($process)) {
            \histou\Basic::returnData(\histou\Debug::errorMarkdownDashboard("# Error: Could not start: $cmd")); // @codeCoverageIgnore
            return 1; // @codeCoverageIgnore
        }
        stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $returnCode = proc_close($process);
        if ($returnCode != 0) {
            \histou\Basic::returnData(\histou\Debug::errorMarkdownDashboard("# '".$cmd."' did not return with returncode 0. Maybe the phpCommand is not set properly."), 1);
            return 1;

        }
        return 0;
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
