<?php
/**
Parses Templates files and generates Code.
PHP version 5
@category Parser
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
namespace histou\template;

/**
Parses Templates files and generates Code.
PHP version 5
@category Parser
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
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
            $jsonDashboard = static::isStringValidJson($dashboard);
            if ($jsonDashboard === null) {
                \histou\Debug::enable();
                return \histou\Debug::errorMarkdownDashboard(
                    '#The Template given was not valid json!'
                );
            }

            $oldPerfData = array('host' => array(), 'service' => array(), 'command' => array());
            if ($jsonDashboard && array_key_exists('rows', $jsonDashboard)) {
                foreach ($jsonDashboard['rows'] as &$row) {
                    if (array_key_exists('panels', $row)) {
                        foreach ($row['panels'] as &$panel) {
                            // remove PanelTitel if needed
                            if (\histou\Basic::$disablePanelTitle) {
                                $panel['title'] = '';
                            }
                            // get old Perfdata
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
            $dashboard = json_encode($jsonDashboard);

            foreach ($oldPerfData as $label => $value) {
                if (sizeof($value) > 0) {
                    $counted = array_count_values($value);
                    $oldPerfData[$label] = array_search(max($counted), $counted);
                }
            }
            //Test if hostname != service != command if so replace them
            $perfDataSize = sizeof($oldPerfData);
            $perfDataKeys = array_keys($oldPerfData);
            $replaced = false;
            for ($i = 0; $i < $perfDataSize; $i++) {
                if ($oldPerfData[$perfDataKeys[$i]] != $oldPerfData[$perfDataKeys[($i+1)%$perfDataSize]]
                    && $oldPerfData[$perfDataKeys[$i]] != $oldPerfData[$perfDataKeys[($i+2)%$perfDataSize]]
                    && array_key_exists($perfDataKeys[$i], $oldPerfData)
                    && array_key_exists($perfDataKeys[$i], $perfData)
                    && $oldPerfData[$perfDataKeys[$i]] != $perfData[$perfDataKeys[$i]]
                ) {
                    $dashboard = str_replace(
                        $oldPerfData[$perfDataKeys[$i]],
                        $perfData[$perfDataKeys[$i]],
                        $dashboard
                    );
                    $replaced = true;
                }
            }
            if (!$replaced) {
                \histou\Debug::add('# Nothing replace because hostname, service, command are equal in the template');
            }
            return $dashboard;
        };

        return array($rule, $genTemplate);
    }

    /**
    Checks if a string is valid json and returns an object if so, null if not.
    @param string $string String to test.
    @return obj or null.
    **/
    public static function isStringValidJson($string)
    {
        if (is_string($string)) {
            $obj = json_decode($string, true);
            if (is_array($obj)) {
                return $obj;
            }
        }
        return null;
    }
}
