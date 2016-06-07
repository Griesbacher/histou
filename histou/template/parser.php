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
            $oldPerfData = array('host' => array(), 'service' => array(), 'command' => array());
            $jsonDashboard = json_decode($dashboard, true);
            if ($jsonDashboard && array_key_exists('rows', $jsonDashboard)) {
                foreach ($jsonDashboard['rows'] as $row) {
                    if (array_key_exists('panels', $row)) {
                        foreach ($row['panels'] as $panel) {
                            if (array_key_exists('targets', $panel)) {
                                foreach ($panel['targets'] as $target) {
                                    if (array_key_exists('tags', $target)) {
                                        foreach ($target['tags'] as $tag) {
                                            $key = $tag['key'];
                                            if (array_key_exists($key, $oldPerfData)) {
                                                array_push($oldPerfData[$key], $tag['value']);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            foreach ($oldPerfData as $label => $value) {
                if (sizeof($value) > 0) {
                    $counted = array_count_values($value);
                    $oldPerfData[$label] = array_search(max($counted), $counted);
                }
            }

            //Test if hostname != service != command if so replace them
            $oldPerfDataSize = sizeof($oldPerfData);
            $oldPerfDataKeys = array_keys($oldPerfData);
            $replaced = false;
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
                    $replaced = true;
                }
            }
            if (!$replaced) {
                \histou\Debug::add('# Nothing replace because hostname, service, command are equal in the template');
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
