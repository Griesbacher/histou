<?php
/**
Parses Templates files and generates Code.
PHP version 5
@category Parser
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\template;

/**
Parses Templates files and generates Code.
PHP version 5
@category Parser
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class Parser
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
            if (substr(trim($line), 0, 1) == "#") {
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
            $keyValueRegex = "/\\\\\"(host|service|command)\\\\\"\\s+=\\s+'(.*?)'\\s+/";
            if (preg_match_all($keyValueRegex, $dashboard, $hits)) {
                $oldPerfData = array();
                foreach ($hits[1] as $key => $value) {
                    if (!array_key_exists($value, $oldPerfData)) {
                        $oldPerfData[$value] = $hits[2][$key];
                    }
                }
                foreach ($oldPerfData as $key => $value) {
                    $dashboard = str_replace(
                        sprintf("\\\"%s\\\" = '%s'", $key, $value),
                        sprintf("\\\"%s\\\" = '%s'", $key, $perfData[$key]),
                        $dashboard
                    );
                }
                //Test if hostname != service != command if so replace them
                $oldPerfDataSize = sizeof($oldPerfData);
                $oldPerfDataKeys = array_keys($oldPerfData);
                for ($i = 0; $i < $oldPerfDataSize; $i++) {
                    if ($oldPerfData[$oldPerfDataKeys[$i]] != $oldPerfData[$oldPerfDataKeys[($i+1)%$oldPerfDataSize]]
                        && $oldPerfData[$oldPerfDataKeys[$i]] != $oldPerfData[$oldPerfDataKeys[($i+2)%$oldPerfDataSize]]
                        && $oldPerfData[$oldPerfDataKeys[$i]] != $perfData[$oldPerfDataKeys[$i]]
                    ) {
                        $dashboard = str_replace(
                            $oldPerfData[$oldPerfDataKeys[$i]],
                            $perfData[$oldPerfDataKeys[$i]],
                            $dashboard
                        );
                    }
                }
            } else {
                //regex does not work
            }
            if (!static::isStringValidJson($dashboard)) {
                \histou\Debug::enable();
                return \histou\Debug::errorMarkdownDashboard(
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
