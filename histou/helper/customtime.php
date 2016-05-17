<?php
/**
Contains Helper Class.
PHP version 5
@category Folder_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\helper;

/**
Helper Class.
PHP version 5
@category Helper_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

class CustomTime
{
	private static $units = array(
							's' => 1,
							'm' => 60,
							'h' => 3600,
							'd' => 86400,
							'M' => 2592000
						);
	public static function convertToSeconds($customTime) {
		preg_match("/^(\\d+)(\\w)$/", $customTime, $matches);
		if (!$matches){
			return -1;
		}
		if (!array_key_exists($matches[2],CustomTime::$units)){
			return -2;
		}
		return $matches[1]*CustomTime::$units[$matches[2]];
	}
	
	public static function getLongestTime(array $durations){
		$absDurations = array();
		foreach ($durations as $d) {
			$absDurations[$d] = CustomTime::convertToSeconds($d);
		}
		if (!empty($absDurations)){
			return array_keys($absDurations, max($absDurations))[0];
		}else{
			return '0s';
		}		
	}
}