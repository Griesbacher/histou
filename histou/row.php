<?php
/**
Contains Row Class.
PHP version 5
@category Row_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
require_once 'panel.php';
/**
Row Class.
PHP version 5
@category Row_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class Row
{
    private $_data = array(
                        'titel' => null,
                        'editable' => true,
                        'height' => null,
                        'panels' => array()
                    );
    private $_panels = array();

    /**
    Constructor.
    @param string $titel  name of the row.
    @param string $height row height.
    @return object
    **/
    function __construct($titel, $height = HEIGHT)
    {
        $this->_data['titel'] = $titel;
        $this->_data['height'] = $height;
    }

    /**
    Creates an array, also from its subelements.
    @return array
    **/
    public function toArray()
    {
        foreach ($this->_panels as $panel) {
            array_push($this->_data['panels'], $panel->toArray());
        }
        return $this->_data;
    }

    /**
    Setter for Editable.
    @param boolean $editable true/false.
    @return null
    **/
    public function setEditable(boolean $editable)
    {
        $this->_data['editable'] = $editable;
    }

    /**
    Adds a new Panel to the row.
    @param object $panel new panel.
    @return null
    **/
    public function addPanel($panel)
    {
        //TODO: Aufvererbung testen
        array_push($this->_panels, $panel);
    }

    /**
    Setter for every property.
    @param string $name  key.
    @param object $value value.
    @return null
    **/
    public function setCustomProperty($name, $value)
    {
        $this->_data[$name] = $value;
    }
}
