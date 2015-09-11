<?php
/**
Contains Dashboard Class.
PHP version 5
@category Dashboard_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
require_once 'row.php';
/**
Dashboard Class.
PHP version 5
@category Dashboard_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class Dashboard
{
    private $_data = array(
    'id' => null,
    'style' => 'light',
    'title' => null,
    'originalTitle' => 'CustomDashboard',
    'tags' => array(),
    'timezone' => 'browser',
    'editable' => true,
    'hideControls' => true,
    'sharedCrosshair' => false,
    'nav' => array(array(
    'type' => 'timepicker',
    'enable' => true,
    'status' => 'Stable',
    'time_options' => array(
    "5m","15m","1h","6h","12h","24h","2d","7d","30d"
    ),
    'refresh_intervals' => array(
    "5s","10s","30s","1m","5m","15m","30m","1h","2h","1d"
    ),
    'now' => true,
    'collapse' => false,
    'notice' => false,
    )),
    'time' => array(
    'from' => 'now-8h',
    'to' => 'now',
    ),
    'templating' => array(),
    'annotations' => array(),
    'refresh' => '5s',
    'version' => '6',
    'hideAllLegends' => false,
    'rows' => array(),
    );
    private $_rows = array();

    /**
    Constructs a new Dashboard.
    @param string $title name of the dashboard.
    @return null
    **/
    function __construct($title)
    {
        $this->_data['title'] = $title;
    }

    /**
    Creates a array, with all sub elements.
    @return array
    **/
    public function toArray()
    {
        foreach ($this->_rows as $row) {
            array_push($this->_data['rows'], $row->toArray());
        }
        return $this->_data;
    }

    /**
    Setter for Editable.
    @param boolean $editable true7false.
    @return null
    **/
    public function setEditable(boolean $editable)
    {
        $this->_data['editable'] = $editable;
    }

    /**
    Setter Everything.
    @param string $name  key.
    @param string $value value.
    @return null
    **/
    public function setCustomProperty($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
    Adds a row to the dashboard.
    @param Row $row new Row.
    @return null
    **/
    public function addRow(Row $row)
    {
        array_push($this->_rows, $row);
    }
}
