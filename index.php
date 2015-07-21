<?php
/**
This is the main file. It returns an json Object when requested per jsonp or some debug output else.
PHP version 5
@category Main_File
@package Default
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
**/

require_once 'histou/template.php';
require_once 'histou/rule.php';
require_once 'histou/folder.php';
require_once 'histou/database.php';
require_once 'histou/debug.php';
require_once 'histou/dashboard.php';

define("DEFAULT_TEMPLATE_FOLDER", "histou/templates/default/");
define("CUSTOM_TEMPLATE_FOLDER", "histou/templates/custom/");

parsIni('histou.ini');


header("access-control-allow-origin: *");
//Disable warnings
//error_reporting(E_ALL ^ E_WARNING);
//error_reporting(0);
ini_set('default_socket_timeout', DEFAULT_SOCKET_TIMEOUT);


parsArgs();
define("INFLUX_QUERY", sprintf("select * from /%s%s%s.*/ limit 1", HOST, INFLUX_FIELDSEPERATOR, SERVICE));


// database load perfdata
$influx = new Influxdb(INFLUX_URL);
$request = $influx->makeRequest(INFLUX_QUERY);
$perfData = $influx->filterPerfdata($request, HOST, SERVICE, '\\'.INFLUX_FIELDSEPERATOR);

if (sizeof($perfData) < 4) {
    returnData(Debug::errorMarkdownDashboard('#Host / Service not found in Influxdb'), 1);
}

// $perfData = array('host' => 'host0', 'service' => 'ping', 'command' => 'ping4', 'perfLabel' => array('rta', 'pl'));

// load templates
$templates = Folder::loadFolders(array(CUSTOM_TEMPLATE_FOLDER, DEFAULT_TEMPLATE_FOLDER));


Rule::setCheck($perfData['host'], $perfData['service'], $perfData['command'], array_keys($perfData['perfLabel']));


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
    }

    $json = json_encode($data);

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
}
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
