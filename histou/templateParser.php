<?php
/**
Parses Templates files and generates Code.
PHP version 5
@category Parser
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
require_once 'histou/rule.php';
/**
Parses Templates files and generates Code.
PHP version 5
@category Parser
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class TemplateParser
{
    /**
    Expects a filename and parses the file, to return a rule and a dashbord lambda.
    @param string $file Path to file.
    @return array.
    **/
    public static function parseSimpleTemplate($file)
    {
        $lines = file($file, FILE_SKIP_EMPTY_LINES);
        $foundJson = false;
        $ruleHits = array();
        $dashboard = "";
        foreach ($lines as $line) {
            if (substr($line, 1) == "#") {
                //Comment found
                continue;
            }
            if ($foundJson) {
                $dashboard .= $line;
            } else {
                if (sizeof($ruleHits) != 4) {
                    //Searching for Ruleset
                    foreach (array('host', 'service', 'command', 'perfLabel') as $type) {
                        if (preg_match(";^\s*$type\s*=\s*(.*?)$;", $line, $hit)) {
                            if ($type == 'perfLabel') {
                                $ruleHits[$type] = str_getcsv($hit[1]);
                                foreach ($ruleHits[$type] as &$label) {
                                    $label = trim($label);
                                }
                            } else {
                                $ruleHits[$type] = trim($hit[1]);
                            }
                        }
                    }
                }
                if (preg_match(";^\s*{;", $line)) {
                    //Found dashboard beginning
                    $foundJson = true;
                    $dashboard .= $line;
                }
            }
        }

        $rule = new Rule(
            $ruleHits['host'],
            $ruleHits['service'],
            $ruleHits['command'],
            $ruleHits['perfLabel']
        );

        $genTemplate = function ($perfData) use ($dashboard) {
            $tablenameRegex = ";(.*\\\\?\\\"+)(.*?)&(.*?)&(.*?)&(.*?)&(.*?)(.*);";
            if (preg_match($tablenameRegex, $dashboard, $hits)) {
                $tempPerfData = array(
                'host' => $hits[2], 'service' => $hits[3], 'command' => $hits[4]
                );
                $dashboard = preg_replace(
                    $tablenameRegex,
                    sprintf(
                        "$1%s%s%s%s%s%s$5%s$6$7",
                        $perfData['host'],
                        INFLUX_FIELDSEPERATOR,
                        $perfData['service'],
                        INFLUX_FIELDSEPERATOR,
                        $perfData['command'],
                        INFLUX_FIELDSEPERATOR,
                        INFLUX_FIELDSEPERATOR
                    ),
                    $dashboard
                );
                $tempPerfDataSize = sizeof($tempPerfData);
                $tempPerfDataKeys = array_keys($tempPerfData);
                for ($i = 0; $i < $tempPerfDataSize; $i++) {
                    if ($tempPerfData[$tempPerfDataKeys[$i]] != $tempPerfData[$tempPerfDataKeys[($i+1)%$tempPerfDataSize]]
                        && $tempPerfData[$tempPerfDataKeys[$i]] != $tempPerfData[$tempPerfDataKeys[($i+2)%$tempPerfDataSize]]
                        && $tempPerfData[$tempPerfDataKeys[$i]] != $perfData[$tempPerfDataKeys[$i]]
                    ) {
                        $dashboard = preg_replace(
                            sprintf(";([^%s])(%s)([^%s]);", INFLUX_FIELDSEPERATOR, $tempPerfData[$tempPerfDataKeys[$i]], INFLUX_FIELDSEPERATOR),
                            sprintf("$1%s$3", $perfData[$tempPerfDataKeys[$i]]),
                            $dashboard
                        );
                    }
                }
            } else {
                //regex does not work
            }
            if (!static::isStringValidJson($dashboard)) {
                Debug::enable();
                return Debug::errorMarkdownDashboard(
                    '#The Template given was not valid json!'
                );
            } else {
                return $dashboard;
            }
        };

        return array($rule, $genTemplate);
    }

    /**
    Checks if a string is valid json and returns a boolean.
    @param string $string String to test.
    @return boolean.
    **/
    public static function isStringValidJson($string)
    {
        return is_string($string) && is_object(json_decode($string)) ? true : false;
    }
}
