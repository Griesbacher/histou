<?php
/**
Contains Rule Class.
PHP version 5
@category Rule_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

/**
Rule Class.
PHP version 5
@category Rule_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class Rule
{
    private $_data = array();
    private static $_check = array();

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
        $this->_data['host'] = $host;
        $this->_data['service'] = $service;
        $this->_data['command'] = $command;
        $this->_data['perfLabel'] = $perfLabel;
        $this->escapeRule(true);
        sort($this->_data['perfLabel'], SORT_NATURAL);
        $this->file = $file;
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
    public function escapeRule($replaceSpecialChars = false)
    {
        foreach ($this->_data as &$entry) {
            if (is_array($entry)) {
                foreach ($entry as &$perfLabel) {
                    if ($replaceSpecialChars) {
                        $perfLabel = $this->_convertSpecialCharsToRegex($perfLabel);
                    }
                    $perfLabel = $this->_createRegex($perfLabel);
                }
            } else {
                if ($replaceSpecialChars) {
                    $entry = $this->_convertSpecialCharsToRegex($entry);
                }
                $entry = $this->_createRegex($entry);
            }
        }
    }

    /**
    Appends semicolons.
    @param string $string string to change.
    @return string
    **/
    private function _createRegex($string)
    {
        return ";$string;";
    }

    /**
    Replaces special Chars/Strings to regex.
    @param string $stringToReplace string to replace.
    @return string.
    **/
    private function _convertSpecialCharsToRegex($stringToReplace)
    {
        $stringToReplace = trim($stringToReplace);
        switch ($stringToReplace){
        case "*":
        case "ALL":
            return ".*";
        case "NONE":
            return "^$";
        default:
        }
        return $stringToReplace;
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
        static::$_check['host'] = $host;
        static::$_check['service']  = $service;
        static::$_check['command']  = $command;
        static::$_check['perfLabel']  = $perfLabel;
        sort(static::$_check['perfLabel'], SORT_NATURAL);
    }

    /**
    Sort function.
    @param object $first  rule one.
    @param object $second rule two.
    @return int.
    **/
    public static function compare($first, $second)
    {
        return static::_compareTwoObjects($first, $second, false);
    }

    /**
    Tests if the template is valid against the given data.
    @return boolean.
    **/
    public function isValid()
    {
        $gen = new Rule(
            static::$_check['host'], static::$_check['service'],
            static::$_check['command'], static::$_check['perfLabel']
        );
        return static::_compareTwoObjects($this, $gen, true)  == 0 ? true : false;
    }

    /**
    Compares two values against a base value.
    @param array   $first  array one.
    @param array   $second array two.
    @param boolean $valid  is the comparison just for validty reasons.
    @return int.
    **/
    private static function _compareTwoObjects($first, $second, $valid)
    {
        $checks = array('host', 'service', 'command', 'perfLabel');
        $result = 0;
        foreach ($checks as $check) {
            $result = static::_compareValue(
                $first->_data[$check], $second->_data[$check],
                static::$_check[$check], $valid
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
    @param boolean $valid  is the comparison just for validty reasons.
    @return int.
    **/
    private static function _compareValue($first, $second, $base, $valid)
    {
        if (is_array($first) && is_array($second) && is_array($base)) {
            $firstStar = static::_starArray($first);
            $secondStar = static::_starArray($second);
            //The array which has the same amount of entries and matching regex will be choosen
            $baseSize = sizeof($base);
            $hitsFirst = static::_compareArrays($first, $base);
            $hitsSecond = static::_compareArrays($second, $base);
            if ($hitsFirst != $hitsSecond || $firstStar != $secondStar) {
                if ($hitsFirst == $baseSize) {
                    return -1;
                }
                if ($hitsSecond == $baseSize) {
                    return 1;
                }
                if ($firstStar) {
                    return -1;
                } else {
                    return 1;
                }
                if ($valid) {
                    return 2;
                }
            }
        } else {
            if ($first != $second) {
                $firstResult = preg_match($first, $base);
                $secondResult = preg_match($second, $base);
                if ($firstResult != $secondResult) {
                    if ($firstResult) {
                        return -1;
                    } else {
                        return 1;
                    }
                }
            }
        }
        return 0;
    }

    /**
    Tests if the array contains only a star.
    @param array $array to test.
    @return boolen.
    **/
    private static function _starArray(array $array)
    {
        return sizeof($array) == 1 && $array[0] == $this->_createRegex('.*');
    }

    /**
    Compares two arrays, returns the amount of elements match.
    @param array $arrayToCompare array one.
    @param array $base           array two.
    @return boolean.
    **/
    private static function _compareArrays(array $arrayToCompare, array $base)
    {
        $hits = 0;
        if (sizeof($arrayToCompare) == sizeof($base)) {
            //TODO: may need a fix, due to the ordering
            for ($i = 0; $i < sizeof($arrayToCompare); $i++) {
                if (preg_match($arrayToCompare[$i], $base[$i])) {
                    $hits++;
                }
            }
        }
        return $hits;
    }

    /**
    Tests if the given tablename matches table.
    @param string $tablename name to test.
    @return boolean.
    **/
    public function matchesTablename($tablename)
    {
        $tableparts = explode(INFLUX_FIELDSEPERATOR, $tablename);
        $keys = array_keys($this->_data);
        for ($i = 0; $i < 3; $i++) {
            if (!preg_match($this->_data[$keys[$i]], $tableparts[$i])) {
                return false;
            }
        }
        foreach ($this->_data[$keys[3]] as $perfLabel) {
            if (preg_match($perfLabel, $tableparts[3])) {
                return true;
            }
        }
        return false;
    }

    /**
    Prints the rule.
    @return string.
    **/
    public function __toString()
    {
        return sprintf(
            "\tHost: %s \n\t\tService: %s\n\t\tCommand: %s\n\t\tPerflabel: %s",
            $this->_data['host'],
            $this->_data['service'],
            $this->_data['command'],
            implode(", ", $this->_data['perfLabel'])
        );
    }
}
