<?php
/**
This is the main file.
It returns an json Object when requested per jsonp or some debug output else.
PHP version 5
@category Main_File
@package Default
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

function __autoload($className) {
    $file = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $className)).'.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

set_error_handler(
    function ($errno, $errstr, $errfile, $errline, array $errcontext) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
);

//Set path to config file
\histou\Basic::parsIni('histou.ini');

header("access-control-allow-origin: *");
//Disable warnings
//error_reporting(E_ALL ^ E_WARNING);
//error_reporting(0);
ini_set('default_socket_timeout', DEFAULT_SOCKET_TIMEOUT);

parsArgs();
// database load perfdata
$influx = new \histou\database\Influxdb(INFLUX_URL);
$request = $influx->fetchPerfData();
$perfData = $influx->filterPerfdata(
    $request,
    HOST,
    SERVICE,
    '\\'.INFLUX_FIELDSEPERATOR
);

$perfDataSize = sizeof($perfData);
if ($perfDataSize < 4) {
    if ($perfDataSize == 1) {
        returnData(
            \histou\Debug::errorMarkdownDashboard(
                '#Influxdb Error: '.$perfData.' Query: '.INFLUX_QUERY
            ),
            1
        );
    } else {
        returnData(
            \histou\Debug::errorMarkdownDashboard('#Host / Service not found in Influxdb'),
            1
        );
    }
}
// load templates
$templateFiles = \histou\Folder::loadFolders(
    array(CUSTOM_TEMPLATE_FOLDER, DEFAULT_TEMPLATE_FOLDER)
);

$templateCache = new \histou\template\cache();
$templates = $templateCache->loadTemplates($templateFiles);

if (sizeof($templates) == 0) {
    returnData(\histou\Debug::errorMarkdownDashboard('#Could not load templates!'), 1);
}

\histou\template\Rule::setCheck(
    $perfData['host'],
    $perfData['service'],
    $perfData['command'],
    array_keys($perfData['perfLabel'])
);

usort($templates, '\histou\template\Template::compare');
$valid = $templates[0]->isValid();
foreach ($templates as $template) {
    \histou\Debug::add($template);
}
\histou\Debug::add("Is the first template valid: ".\histou\Debug::printBoolean($valid));

if ($valid) {
    $template = $templates[0];
} else {
    $template = \histou\template\Template::findDefaultTemplate($templates, 'default.php');
}
if (isset($template) && !empty($template)) {
    $className = get_class($template);
    if ($className == 'histou\template\Rule') {
        $dashboard = \histou\template\loader::loadTemplate($template->getFileName(), true)->generateDashboard($perfData);
    } elseif ($className == 'histou\template\Template' || $className == 'histou\template\SimpleTemplate') {
        $dashboard = $template->generateDashboard($perfData);
    } else {
        returnData(\histou\Debug::errorMarkdownDashboard("# unkown class $className"), 1);
    }

    if ($dashboard == null) {
        returnData(\histou\Debug::errorMarkdownDashboard('# Template did not return a dashboard!'), 1);
    } else {
        returnData($dashboard, 0);
    }
} else {
    returnData(\histou\Debug::errorMarkdownDashboard('# No template found!'), 1);
}

/**
This function will print its input and exit with the given returncode.
@param object $data       This object will be converted to json.
@param int    $returnCode The returncode the programm will exit.
@return null.
**/
function returnData($data, $returnCode = 0)
{
    if (is_object($data) && get_class($data) == 'histou\grafana\Dashboard') {
        if (\histou\Debug::isEnable()) {
            $data->addRow(\histou\Debug::genMarkdownRow(\histou\Debug::getLogAsMarkdown(), 'Debug'));
        }
        $data = $data->toArray();
        $json = json_encode($data);
    } elseif (is_string($data)) {
        $json = $data;
    }else{
        echo '<pre>';
        print_r("Don't know what to do with this: $data");
        echo '</pre>';
        exit -1;
    }

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
    exit($returnCode);
}
/**
Parses the GET parameter.
@return null.
**/
function parsArgs()
{
    $shortopts  = "";
    $longopts  = array(
    "host:",
    "service:",
    );
    $args = getopt($shortopts, $longopts);
    if (isset($_GET['host']) && !empty($_GET['host'])) {
        define("HOST", $_GET["host"]);
    } elseif (isset($args['host']) && !empty($args['host'])) {
        define("HOST", $args["host"]);
    } else {
        returnData('Hostname is missing!', 1, 'Hostname is missing!');
    }
    if (isset($_GET['service']) && !empty($_GET['service'])) {
        define("SERVICE", $_GET["service"]);
    } elseif (isset($args['service']) && !empty($args['service'])) {
        define("SERVICE", $args["service"]);
    } else {
        define("SERVICE", "");
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
