<?php
/**
Contains types of SinglestatPanels.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
namespace histou\grafana\singlestatpanel;

/**
Base Panel.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
abstract class SinglestatPanel extends \histou\grafana\Panel
{
    /**
    Constructor.
    @param string  $title      name of the panel.
    @param boolean $legendShow hide the legend or not
    @return object.
    **/
    public function __construct($title, $id = -1)
    {
        parent::__construct($title, 'singlestat', $id);
        $this->data['targets'] = array();
        $this->data['rangeMaps'] = array();
        $this->data['valueMaps'] = array();
    }
    /**
    Adds the target to the dashboard.
    **/
    public function addTarget($target)
    {
        if (!empty($target)) { //TODO:check
            array_push($this->data['targets'], $target);
        }
    }
    /**
    Changes the color of the value and/or value.
    @param array $colors array of hexcolor.
    @param boolean $background change background color.
    @param boolean $value change value color.
    @return null.
    **/
    public function setColor($colors, $background = true, $value = false)
    {
        $this->data['colorBackground'] = $background;
        $this->data['colorValue'] = $value;
        $this->data['colors'] = $colors;
    }
    /**
    Sets Thresholds e.g. for backgroundcolors.
    @param string $first first threshold.
    @param string $second second threshold.
    @return null.
    **/
    public function setThresholds($first, $second = '')
    {
        $this->data['thresholds'] = $first;
        if ($second != '') {
            $this->data['thresholds'] .= ",$second";
        }
    }
    /**
    Repaces numberranges with text.
    @param float $from start threshold.
    @param float $to end threshold.
    @param string $text to be displayed.
    @return null.
    **/
    public function addRangeToTextElement($from, $to, $text)
    {
        $this->data['mappingType'] = 2;
        array_push(
            $this->data['rangeMaps'],
            array('from' => $from, 'to' => $to, 'text' => $text)
        );
    }
    /**
    Repaces numbers with text.
    @param float $from start threshold.
    @param float $to end threshold.
    @param string $text to be displayed.
    @return null.
    **/
    public function addValueToTextElement($value, $text)
    {
        $this->data['mappingType'] = 1;
        array_push(
            $this->data['valueMaps'],
            array('op' => '=', 'value' => $value, 'text' => $text)
        );
    }
}
