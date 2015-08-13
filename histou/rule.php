<?php
class Rule
{
    private $_data = array();
    private static $_check = array();

    public function __construct($host = ';^$;', $service = ';^$;', $command = ';^$;', array $perfLabel) 
    {
        $this->_data['host'] = $host;
        $this->_data['service'] = $service;
        $this->_data['command'] = $command;
        $this->_data['perfLabel'] = $perfLabel;
    }
    
    public function escapeRule()
    {
        // ';^\/.*\/$;';
        foreach ($this->_data as &$entry) {
            if (is_array($entry)) {
                foreach ($entry as &$perfLabel) {
                    $perfLabel = ";$perfLabel;";
                }
            } else {
                $entry = ";$entry;";
            }
        }        
    }

    public static function setCheck($host, $service, $command, array $perfLabel)
    {
        static::$_check['host'] = $host;
        static::$_check['service']  = $service;
        static::$_check['command']  = $command;
        static::$_check['perfLabel']  = $perfLabel;
    }

    public static function compare($first, $second)
    {
        return static::_compareTwoObjects($first, $second, false);
    }
    
    public function isValid()
    {
        $gen = new Rule(static::$_check['host'], static::$_check['service'], static::$_check['command'], static::$_check['perfLabel']);
        $gen->escapeRule();
        return static::_compareTwoObjects($this, $gen, true);
    }
    
    private static function _compareTwoObjects($first, $second, $valid)
    {
        $checks = array('host','service', 'command','perfLabel');
        $result = 0;
        foreach ($checks as $check) {
            $result = static::_compareValue($first->_data[$check], $second->_data[$check], static::$_check[$check], $valid);
            if ($result != 0) {
                return $result;
            }
        }
        return $result;
    }

    private static function _compareValue($first, $second, $base, $valid)
    {
        if (is_array($first) && is_array($second) && is_array($base)) {
            $baseSize = sizeof($base);
            $hitsFirst = static::_compareArrays($first, $base);
            $hitsSecond = static::_compareArrays($second, $base);
            if ($hitsFirst != $hitsSecond) {
                if ($hitsFirst == $baseSize) {
                    return -1;
                }
                if ($hitsSecond == $baseSize) {
                    return 1;
                }
				if ($valid) {
					return 2;
				}
            }
        } else {
            if ($first != $second) {
				//print_r("comp: ".$first." - ".$second." - ".$base."<br>");
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
    
    private static function _compareArrays($first, $base)
    {
        $hits = 0;
        if (sizeof($first) == sizeof($base)) {
            foreach ($base as $basePerfLabel) {
                foreach ($first as $perfLabel) {
                    if (preg_match($perfLabel, $basePerfLabel)) {
                        $hits++;
                    }
                }
            }
        }
        return $hits;
    }
	
	public function matchesTablename($tablename)
	{
		$tableparts = explode(INFLUX_FIELDSEPERATOR, $tablename);
		$keys = array_keys($this->_data);
		for ($i = 0; $i < 3; $i++) {			
			if (!preg_match($this->_data[$keys[$i]], $tableparts[$i])) {
				return false;
			}
		}
		foreach($this->_data[$keys[3]] as $perfLable) {
			if (preg_match($perfLable, $tableparts[3])) {
				return true;
			}
		}
		return false;
	}	
	
    public function __toString()
    {
        return sprintf("\tHost: %s \n\t\tService: %s\n\t\tCommand: %s\n\t\tPerflabel: %s", $this->_data['host'], $this->_data['service'], $this->_data['command'], implode(", ", $this->_data['perfLabel']));
    }
}
?>
