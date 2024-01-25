<?php
/**
Contains types of Templates.
PHP version 5
@category Template_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
namespace histou\template;

/**
Base Class Template.
PHP version 5
@category Template_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
class ForecastTemplate extends Template
{
    private $jsonRules;
    public static $config;
    /**
    Creates a ForecastTemplate.
    @param string  $file        Path to the templatefile.
    @param object  $rule        ruleset.
    @param string  $jsonRules   JSON String.
    @return object.
    **/
    public function __construct($file, Rule $rule, $jsonRules)
    {
        parent::__construct($file, $rule, function ($perfData) {
        });
        $this->jsonRules = $jsonRules;
    }
    
    public function setForecastDurations()
    {
        ForecastTemplate::$config = array();
        foreach (json_decode($this->jsonRules) as $obj) {
            if (property_exists($obj, 'forecast_range') && property_exists($obj, 'method') && property_exists($obj, 'label')) {
                ForecastTemplate::$config[$obj->{'label'}] = array(
                    'method' => $obj->{'method'},
                    'forecast' => $obj->{'forecast_range'}
                );
            }
        }
    }
    
    public function getJSON()
    {
        return $this->jsonRules;
    }
}
