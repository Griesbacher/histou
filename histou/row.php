<?php
require_once 'panel.php';
class Row
{
    private $_data = array(
                        'titel' => null,
                        'editable' => false,
                        'height' => null,
                        'panels' => array()
                    );
    private $_panels = array();
    function __construct($titel, $height = '400px')
    {
        $this->_data['titel'] = $titel;
        $this->_data['height'] = $height;
    }

    public function toArray()
    {
        foreach ($this->_panels as $panel) {
            array_push($this->_data['panels'], $panel->toArray());
        }
        return $this->_data;
    }
    
    public function setEditable(boolean $editable)
    {
        $this->_data['editable'] = $editable;
    }
    public function addPanel($panel)
    {
        //TODO: Aufvererbung testen
        array_push($this->_panels, $panel);        
    }
	public function setCustomProperty($name, $value)
    {
        $this->_data[$name] = $value;
    }
}
?>
