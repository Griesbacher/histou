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

require_once 'histou/basic.php';
require_once 'histou/template.php';
require_once 'histou/folder.php';
require_once 'histou/database.php';
require_once 'histou/debug.php';
require_once 'histou/dashboard.php';

//Set path to config file
parsIni('histou.ini');

header("access-control-allow-origin: *");
//Disable warnings
//error_reporting(E_ALL ^ E_WARNING);
//error_reporting(0);
ini_set('default_socket_timeout', DEFAULT_SOCKET_TIMEOUT);


parsArgs();
define(
    "INFLUX_QUERY",
    sprintf(
        "show series from /%s%s%s.*/",
        str_replace("/", '\/', HOST),
        INFLUX_FIELDSEPERATOR,
        str_replace('/', '\/', SERVICE)
    )
);
// database load perfdata
$influx = new Influxdb(INFLUX_URL);
$request = $influx->makeRequest(INFLUX_QUERY);
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
            Debug::errorMarkdownDashboard(
                '#Influxdb Error: '.$perfData[0].' Query: '.INFLUX_QUERY
            ),
            1
        );
    } else {
        returnData(
            Debug::errorMarkdownDashboard('#Host / Service not found in Influxdb'),
            1
        );
    }
}

// load templates
$templates = Folder::loadFolders(
    array(CUSTOM_TEMPLATE_FOLDER, DEFAULT_TEMPLATE_FOLDER)
);

Rule::setCheck(
    $perfData['host'],
    $perfData['service'],
    $perfData['command'],
    array_keys($perfData['perfLabel'])
);


usort($templates, 'Template::compare');
$valid = $templates[0]->isValid();
foreach ($templates as $template) {
    Debug::add($template);
}
Debug::add("Is the first template valid: ".Debug::printBoolean($valid));

if ($valid) {
    $template = $templates[0];
} else {
    $template = Template::findDefaultTemplate($templates, 'default.php');
}

if (isset($template) && !empty($template)) {
    returnData($template->getTemplate($perfData), 0, 'OK');
} else {
    returnData(Debug::errorMarkdownDashboard('#No template found!'), 1);
}

/**
This function will print its input and exit with the given returncode.
@param object $data       This object will be converted to json.
@param int    $returnCode The returncode the programm will exit.
@return null.
**/
function returnData($data, $returnCode = 0)
{

    if ($data instanceof Dashboard) {
        if (Debug::isEnable()) {
            $data->addRow(Debug::genMarkdownRow(Debug::getLogAsMarkdown(), 'Debug'));
        }
        $data = $data->toArray();
        $json = json_encode($data);
    } else {
        $json = $data;
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
    if (isset($_GET['host']) && !empty($_GET['host'])) {
        define("HOST", $_GET["host"]);
    } else {
        returnData('Hostname is missing!', 1, 'Hostname is missing!');
    }
    if (isset($_GET['service']) && !empty($_GET['service'])) {
        define("SERVICE", $_GET["service"]);
    } else {
        define("SERVICE", "");
    }
    if (isset($_GET['debug'])) {
        Debug::enable();
    }
    if (isset($_GET['height']) && !empty($_GET['height'])) {
        define("HEIGHT", $_GET["height"]);
    } else {
        define("HEIGHT", "400px");
    }
    if (isset($_GET['legend']) && !empty($_GET['legend'])) {
        if ($_GET["legend"] == "true") {
            define("LEGEND_SHOW", true);
        } else {
            define("LEGEND_SHOW", false);
        }
    } else {
        define("LEGEND_SHOW", true);
    }
}
