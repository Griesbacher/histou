<?php
/**
Contains Rule Class.
PHP version 5
@category Rule_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
namespace histou\template;

/**
Rule Class.
PHP version 5
@category Rule_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
class Rule
{
    private $data = array();
    private static $check = array();

    /**
    The file where the rule is from.
    @var string.
    **/
    public $file;

    /**
    Constructs a new Rule.
    @param string $host      hostname.
    @param string $service   servicename.
    @param string $command   commandname.
    @param array  $perfLabel hostname.
    @param string $file      path to the rule.
    @return null
    **/
    public function __construct($host = '^$', $service = '^$', $command = '^$', array $perfLabel = array(), $file = "")
    {
        $this->data['host'] = $host;
        $this->data['service'] = $service;
        $this->data['command'] = $command;
        $this->data['perfLabel'] = $perfLabel;
        static::prepareRule($this->data, true);
        usort($this->data['perfLabel'], "strnatcmp");
        $this->file = $file;
    }

    /**
    Returns the basename of the file.
    @return string.
    **/
    public function getBaseName()
    {
        return basename($this->file);
    }

    /**
    Returns the whole filename.
    @return string.
    **/
    public function getFileName()
    {
        return $this->file;
    }

    /**
    Escaps every regex with semicolons.
    @param boolean $replaceSpecialChars if specialchars should be repaced.
    @return null
    **/
    private static function prepareRule(array &$data, $replaceSpecialChars = false)
    {
        foreach ($data as &$entry) {
            if (is_array($entry)) {
                foreach ($entry as &$perfLabel) {
                    static::replaceVariables($perfLabel);
                    if ($replaceSpecialChars) {
                        static::convertSpecialCharsToRegex($perfLabel);
                    }
                    $perfLabel = static::createRegex($perfLabel);
                }
            } else {
                static::replaceVariables($entry);
                if ($replaceSpecialChars) {
                    static::convertSpecialCharsToRegex($entry);
                }
                $entry = static::createRegex($entry);
            }
        }
    }

    /**
    Replace variables within rulevalues
    @param string $string string to change.
    @return null
    **/
    private static function replaceVariables(&$string)
    {
        if (sizeof(static::$check) == 4) {
            foreach (array('host', 'service', 'command') as $key) {
                $search = SPECIAL_CHAR.$key.SPECIAL_CHAR;
                if (strpos($string, $search) !== false) {
                    $string = str_replace($search, static::$check[$key], $string);
                }
            }
        }
    }

    /**
    Appends semicolons.
    @param string $string string to change.
    @return string
    **/
    private static function createRegex($string)
    {
        return ";$string;";
    }

    /**
    Replaces special Chars/Strings to regex.
    @param string $stringToReplace string to replace.
    @return string.
    **/
    private static function convertSpecialCharsToRegex(&$stringToReplace)
    {
        $stringToReplace = trim($stringToReplace);
        switch ($stringToReplace) {
            case "*":
                //fallthough
            case "ALL":
                $stringToReplace = ".*";
                break;
            case "NONE":
                $stringToReplace = "^$";
                break;
            default:
        }
    }

    /**
    Sets the basic data, against which will be tested.
    @param string $host      hostname.
    @param string $service   servicename.
    @param string $command   commandname.
    @param array  $perfLabel hostname.
    @return null
    **/
    public static function setCheck($host, $service, $command, array $perfLabel)
    {
        static::$check['host'] = $host;
        static::$check['service']  = $service;
        static::$check['command']  = $command;
        usort($perfLabel, "strnatcmp");
        static::$check['perfLabel']  = $perfLabel;
    }

    /**
    Sort function.
    @param object $first  rule one.
    @param object $second rule two.
    @return int.
    **/
    public static function compare($first, $second)
    {
        return static::compareTwoObjects($first, $second);
    }

    /**
    Tests if the template is valid against the given data.
    @return boolean.
    **/
    public function isValid()
    {
        if (preg_match($this->data['host'], static::$check['host'])) {
            if (preg_match($this->data['service'], static::$check['service'])) {
                if (preg_match($this->data['command'], static::$check['command'])) {
                    if (sizeof($this->data['perfLabel']) > sizeof(static::$check['perfLabel'])) {
                        return false;
                    } elseif ($this->starArray($this->data['perfLabel'])) {
                        return true;
                    } else {
                        $hits = 0;
                        foreach (static::$check['perfLabel'] as $perfData) {
                            foreach ($this->data['perfLabel'] as $perfRule) {
                                if (preg_match($perfRule, $perfData)) {
                                    $hits++;
                                    break;
                                }
                            }
                        }
                        if ($hits == sizeof(static::$check['perfLabel'])) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
    Compares two values against a base value.
    @param array   $first  array one.
    @param array   $second array two.
    @param boolean $valid  is the comparison just for validty reasons.
    @return int.
    **/
    private static function compareTwoObjects($first, $second)
    {
        $checks = array('host', 'service', 'command', 'perfLabel');
        $result = 0;
        foreach ($checks as $check) {
            $result = static::compareValue(
                $first->data[$check],
                $second->data[$check],
                static::$check[$check]
            );
            if ($result != 0) {
                return $result;
            }
        }
        return $result;
    }

    /**
    Compares two values against a base value.
    @param object  $first  array one.
    @param object  $second array two.
    @param array   $base   array base.
    @return int.
    **/
    private static function compareValue($first, $second, $base)
    {
        if (is_array($first) && is_array($second) && is_array($base)) {
            $firstStar = static::containsStar($first);
            $secondStar = static::containsStar($second);
            //The array which has the same amount of entries and matching regex will be choosen
            $baseSize = sizeof($base);
            $hitsFirst = static::compareArrays($first, $base);
            $hitsSecond = static::compareArrays($second, $base);

            if ($hitsFirst != $hitsSecond) {
                if ($hitsFirst == $baseSize) {
                    return -1;
                }
                if ($hitsSecond == $baseSize) {
                    return 1;
                }
            }
            //Stars have a reverted logic whome with a star loses
            if ($firstStar && !$secondStar) {
                return 1;
            } elseif ($secondStar) {
                return -1;
            }
        } else {
            if ($first != $second) {
                $firstResult = preg_match($first, $base);
                $secondResult = preg_match($second, $base);
                if ($firstResult != $secondResult) {
                    if ($firstResult) {
                        return -1;
                    } else {
                        return 1;// @codeCoverageIgnore
                    }
                }
                if (static::containsStar($first)) {
                    return 1;
                } elseif (static::containsStar($second)) {
                    return -1;
                }
            } // @codeCoverageIgnore
        }
        return 0;
    }

    /**
    Tests if the object contains an star.
    @param object $toTest array or string to test.
    @return bool true if contains start.
    **/
    private static function containsStar($toTest)
    {
        if (is_array($toTest)) {
            return static::starArray($toTest);
        } else {
            return $toTest == static::createRegex('.*');
        }
    }

    /**
    Tests if the array contains only a star.
    @param array $array to test.
    @return boolen.
    **/
    private static function starArray(array $array)
    {
        return in_array(static::createRegex('.*'), $array);
    }

    /**
    Compares two arrays, returns the amount of elements match.
    @param array $arrayToCompare array one.
    @param array $base           array two.
    @return boolean.
    **/
    private static function compareArrays(array $arrayToCompare, array $base)
    {
        $hits = 0;
        if (sizeof($arrayToCompare) <= sizeof($base)) {
            foreach ($base as $baseLabel) {
                foreach ($arrayToCompare as $comapareLabel) {
                    if (preg_match($comapareLabel, $baseLabel)) {
                        $hits++;
                        break;
                    }
                }
            }
        }
        return $hits;
    }

    /**
    Prints the rule.
    @return string.
    **/
    public function __toString()
    {
        return sprintf(
            "\tFile:\t".$this->file.":\n\t\tHost: %s\n\t\tService: %s\n\t\tCommand: %s\n\t\tPerflabel: %s",
            $this->data['host'],
            $this->data['service'],
            $this->data['command'],
            implode(", ", $this->data['perfLabel'])
        );
    }
}
