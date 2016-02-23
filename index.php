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

require_once('histou/bootstrap.php');

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

//Parse commandline and get parameter
\histou\Basic::parsArgs();

header("access-control-allow-origin: *");
//Disable warnings
//error_reporting(E_ALL ^ E_WARNING);
//error_reporting(0);
ini_set('default_socket_timeout', DEFAULT_SOCKET_TIMEOUT);


// database load perfdata
$perfData = null;
$database = null;
if (DATABASE_TYPE == INFLUXDB) {
	$database = new \histou\database\Influxdb(URL);
}elseif(DATABASE_TYPE == ELASTICSEARCH){
	$database = new \histou\database\Elasticsearch(URL);
}else{
	\histou\Basic::returnData(\histou\Debug::errorMarkdownDashboard('# Unsupported database'), 1);
}

$request = $database->fetchPerfData();
if (empty($request)) {
	\histou\Basic::returnData(\histou\Debug::errorMarkdownDashboard('#Database not reachable or empty result'), 1);
	exit(0);
}
$perfData = $database->filterPerfdata(
	$request,
	HOST,
	SERVICE
);

$perfDataSize = sizeof($perfData);
if ($perfDataSize < 4) {
    if ($perfDataSize == 1) {
        \histou\Basic::returnData(\histou\Debug::errorMarkdownDashboard('#Database Error: '.$perfData), 1);
    } else {
        \histou\Basic::returnData(\histou\Debug::errorMarkdownDashboard('#Host / Service not found in Database'), 1);
    }
}
//save databaseresult to rule
\histou\template\Rule::setCheck(
    $perfData['host'],
    $perfData['service'],
    $perfData['command'],
    array_keys($perfData['perfLabel'])
);

// load templates
$templateFiles = \histou\Folder::loadFolders(
    array(CUSTOM_TEMPLATE_FOLDER, DEFAULT_TEMPLATE_FOLDER)
);

$templateCache = new \histou\template\cache();
$templates = $templateCache->loadTemplates($templateFiles);

if (sizeof($templates) == 0) {
    \histou\Basic::returnData(\histou\Debug::errorMarkdownDashboard('#Could not load templates!'), 1);
}


usort($templates, '\histou\template\Template::compare');
$valid = $templates[0]->isValid();
\histou\Debug::add("Template order:");
foreach ($templates as $template) {
    \histou\Debug::add($template);
}
\histou\Debug::add("Is the first template valid: ".\histou\Debug::printBoolean($valid)."\n");
\histou\Debug::add("Data: ".print_r($perfData, true));

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
        \histou\Basic::returnData(\histou\Debug::errorMarkdownDashboard("# unkown class $className"), 1);
    }

    if ($dashboard == null) {
        \histou\Basic::returnData(\histou\Debug::errorMarkdownDashboard('# Template did not return a dashboard!'), 1);
    } else {
        \histou\Basic::returnData($dashboard, 0);
    }
} else {
    \histou\Basic::returnData(\histou\Debug::errorMarkdownDashboard('# No template found!'), 1);
}
