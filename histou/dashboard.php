<?php
require_once 'row.php';
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
    'hideControls' => false,
    'sharedCrosshair' => false,
    'nav' => array(
    'type' => 'timepicker',
    'enable' => true,
    'status' => 'Stable',
    'time_options' => array("5m","15m","1h","6h","12h","24h","2d","7d","30d"),
    'refresh_intervals' => array("5s","10s","30s","1m","5m","15m","30m","1h","2h","1d"),
    'now' => true,
    'collapse' => false,
    'notice' => false,
    ),
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
    
    function __construct($title)
    {
        $this->_data['title'] = $title;
    }

    public function toArray()
    {
        foreach ($this->_rows as $row) {
            array_push($this->_data['rows'], $row->toArray());
        }
        return $this->_data;
    }
    
    public function setEditable(boolean $editable)
    {
        $this->_data['editable'] = $editable;
    }
	
	public function setCustomProperty($name, $value)
    {
        $this->_data[$name] = $value;
    }
	
    public function addRow(Row $row)
    {
        array_push($this->_rows, $row);        
    }
}
?>
