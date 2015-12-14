<?php
/**
Contains types of Panels.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\grafana;

/**
Base Panel.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
abstract class Panel
{
    protected $data = array(
                        'title' => null,
                        'type' => null,
                        'span' => 12,
                        'editable' => true,
                    );
    protected static $currentId = 1;
    /**
    Constructor.
    @param string $title name of the panel.
    @param string $type  type of the panel.
    @return object.
    **/
    public function __construct($title, $type, $id = -1)
    {
        $this->data['title'] = $title;
        $this->data['type'] = $type;
        if ($id < 1) {
            $id = self::$currentId++;
        }
        $this->data['id'] = $id;
    }

    /**
    Creates an array.
    @return array
    **/
    public function toArray()
    {
        return $this->data;
    }

    /**
    Setter for Spansize
    @param int $spanSize Spansize.
    @return null.
    **/
    public function setSpan($spanSize)
    {
        $this->data['span'] = $spanSize;
    }

    /**
    Setter for editable
    @param boolean $editable .
    @return null.
    **/
    public function setEditable($editable)
    {
        $this->data['editable'] = $editable;
    }

    /**
    Setter for everything
    @param string $name  key.
    @param string $value value.
    @return null.
    **/
    public function setCustomProperty($name, $value)
    {
        $this->data[$name] = $value;
    }
}
