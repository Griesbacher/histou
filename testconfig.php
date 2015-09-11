<?php
/**
This File can be used to test templates, on which host/service they match.
PHP version 5
@category Debug_File
@package Default
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
require_once 'histou/basic.php';
require_once 'histou/database.php';
require_once 'histou/folder.php';
$lineEnd = '';
if (isset($argv[1]) && !empty($argv[1])) {
    $requestedTemplate = $argv[1];
    $lineEnd = "\n";
} elseif (isset($_GET["filename"]) && !empty($_GET["filename"])) {
    $requestedTemplate = $_GET["filename"];
    $lineEnd = '<br>';
} else {
    print_r(
        "Arguement is missing!
When used by URL pass the Templatefilename by the Postparameter 'filename'.
On Commandline pass it as the first argument"
    );
    exit(1);
}

parsIni('histou.ini');

$influx = new Influxdb(INFLUX_URL);
$series = getSeries($influx);

$availableTemplates = Folder::loadFolders(
    array(CUSTOM_TEMPLATE_FOLDER, DEFAULT_TEMPLATE_FOLDER)
);

$templatesToCheck = array();
foreach ($availableTemplates as $template) {
    if ($template->getSimpleFileName() == $requestedTemplate
        || $template->getFileName() == $requestedTemplate
    ) {
        array_push($templatesToCheck, $template);
    }
}

if (count($templatesToCheck) == 0) {
    print_r('No template found with this name: '.$requestedTemplate.$lineEnd);
    exit(0);
}

foreach ($templatesToCheck as $template) {
    $hits = array();
    foreach ($series as $tablename) {
        if ($template->matchesTablename($tablename)) {
            array_push($hits, preg_split('/&(?!.*&).*/', $tablename)[0]);
        }
    }
    $hits = array_unique($hits);
    
    print_r($template->getPath().'/'.$template->getFileName().":".$lineEnd);
    print_r('----'.$lineEnd);
    foreach ($hits as $hit) {
        print_r($hit.$lineEnd);
    }
}



/**
Gets all series names from influxdb.
@param object $database Influxdb Object.
@return List of names.
**/
function getSeries($database)
{
    $list = array();
    $request = $database->makeRequest("SHOW SERIES");
    foreach ($request[0]['series'] as $tablename) {
        array_push($list, $tablename['name']);
    }
    return $list;
}
