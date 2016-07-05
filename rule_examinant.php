<?php
/**
Examinates rule files and returnes the best.
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

// load ForecastTemplates
$forecastTemplateFiles = \histou\Folder::loadFolders(
    array(FORECAST_TEMPLATE_FOLDER)
);
$forcastTemplateCache = new \histou\template\cache('cli');
$forecastTemplates = $forcastTemplateCache->loadTemplates($forecastTemplateFiles, '\histou\template\loader::loadForecastTemplate');

if (\histou\Basic::$request) {
    $result = "[";
    foreach (\histou\Basic::$request as $index => $value) {
        \histou\template\Rule::setCheck($value['host'], $value['service'], $value['command'], $value['perf_labels']);
        usort($forecastTemplates, '\histou\template\Template::compare');
        $fValid = $forecastTemplates[0]->isValid();
        \histou\Debug::add("ForecastTemplate order:");
        foreach ($forecastTemplates as $ftemplate) {
            \histou\Debug::add($ftemplate);
        }
        \histou\Debug::add("Is the first ForecastTemplate valid: ".\histou\Debug::printBoolean($fValid)."\n");

        if ($fValid) {
            $forecastTemplate = $forecastTemplates[0];
            $className = get_class($forecastTemplate);
            if ($className == 'histou\template\Rule') {
                $forecastTemplate = \histou\template\loader::loadForecastTemplate($forecastTemplate->getFileName(), true);
            }
            $result .= $forecastTemplate->getJSON();
        } else {
            $result .= "[]";
        }
        $result .= ",";
    }
    $result = rtrim($result, ",");
    $result .= "]";
    echo $result;
} else {
    \histou\template\Rule::setCheck(HOST, SERVICE, COMMAND, $PERF_LABEL);
    usort($forecastTemplates, '\histou\template\Template::compare');
    $fValid = $forecastTemplates[0]->isValid();
    \histou\Debug::add("ForecastTemplate order:");
    foreach ($forecastTemplates as $ftemplate) {
        \histou\Debug::add($ftemplate);
    }
    \histou\Debug::add("Is the first ForecastTemplate valid: ".\histou\Debug::printBoolean($fValid)."\n");

    if ($fValid) {
        $forecastTemplate = $forecastTemplates[0];
        $className = get_class($forecastTemplate);
        if ($className == 'histou\template\Rule') {
            $forecastTemplate = \histou\template\loader::loadForecastTemplate($forecastTemplate->getFileName(), true);
        }
        echo $forecastTemplate->getJSON();
    } else {
        echo "[]";
    }
}
