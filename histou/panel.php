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
                        'editable' => false,
                        'legend' => array(
                        'current' => true,
                        'max' => true,
                        'min' => true,
                        'show' => true,
                        'values' => true,
                        )
                    );

    /**
    Constructor.
    @param string $title name of the panel.
    @param string $type  type of the panel.
    @return object.
    **/
    function __construct($title, $type)
    {
        $this->data['title'] = $title;
        $this->data['type'] = $type;
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
    public function setEditable(boolean $editable)
    {
        $this->data['editable'] = $editable;
    }

    /**
    Setter for Id
    @param int $id Id.
    @return null.
    **/
    public function setId($id)
    {
        $this->data['id'] = $id;
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

/**
Base Panel.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class TextPanel extends Panel
{
    const MARKDOWN = 'markdown';
    const TEXT = 'text';
    const HTML = 'html';

    /**
    Constructor.
    @param string $title name of the panel.
    @return object.
    **/
    function __construct($title)
    {
        parent::__construct($title, 'text');
    }

    /**
    Setter for Mode
    @param int $mode Markdown,text,html.
    @return null.
    **/
    public function setMode($mode)
    {
        $this->data['mode'] = $mode;
    }

    /**
    Setter for Content
    @param int $content content.
    @return null.
    **/
    public function setContent($content)
    {
        $this->data['content'] = $content;
    }
}

/**
Base Panel.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class GraphPanel extends Panel
{
    /**
    Constructor.
    @param string $title name of the panel.
    @return object.
    **/
    function __construct($title)
    {
        parent::__construct($title, 'graph');
        $this->data['tooltip'] = array(
                                'show' =>  false,
                                'values' =>  false,
                                'min' =>  false,
                                'max' =>  false,
                                'current' =>  false,
                                'total' =>  false,
                                'avg' =>  false,
                                'shared' =>  true
                            );
        $this->data['legend'] = array(
                                "show" => true,
                                "values" => false,
                                "min" => false,
                                "max" => false,
                                "current" => false,
                                "total" => false,
                                "avg" => false,
                                "alignAsTable" => false,
                                "rightSide" => false,
                                "hideEmpty" => true
                            );
        $this->data['fill'] = 0;
        $this->data['linewidth'] = 2;
        $this->data['targets'] = array();
    }

    /**
    Setter for setTooltip
    @param int $tooltip setTooltip.
    @return null.
    **/
    public function setTooltip(array $tooltip)
    {
        $this->data['tooltip'] = tooltip;
    }

    /**
    Adds an 'line' to the panel.
    @param string $target tablename.
    @param string $alias  alias.
    @param array  $tags   tags for the query.
    @return null.
    **/
    public function addTargetSimple($target, $alias = "", array $tags = array())
    {
        array_push(
            $this->data['targets'],
            array(
            "function" => "mean",
            "column" => "value",
            "measurement" => sprintf($target),
            "query" => sprintf(
                'select mean(value) from "%s" where AND $timeFilter group by time($interval)',
                $target
            ),
            "alias" => $alias,
            "tags" => $tags
            )
        );
    }

    /**
    Changes the color of a line.
    @param int    $alias linename.
    @param string $color hexcolor.
    @return null.
    **/
    public function addAliasColor($alias, $color)
    {
        if (!isset($this->data['aliasColors'])) {
            $this->data['aliasColors'] = array();
        }
        $this->data['aliasColors'][$alias] = $color;
    }

    /**
    Setter for setleftYAxisLabel
    @param int $label Linewidth.
    @return null.
    **/
    public function setleftYAxisLabel($label)
    {
        $this->data['leftYAxisLabel'] = $label;
    }

    /**
    Adds any threshold lines
    @param string $host      hostname.
    @param string $service   servicename.
    @param string $command   commandname.
    @param array  $perfLabel hostname.
    @param string $name      line label.
    @param string $color     hexcolor.
    @return null.
    **/
    private function _addThreshold($host, $service, $command, $perfLabel, $name, $color)
    {
        foreach (array('normal', 'min', 'max') as $tag) {
            $target = sprintf(
                '%s%s%s%s%s%s%s%s%s',
                $host, INFLUX_FIELDSEPERATOR,
                $service, INFLUX_FIELDSEPERATOR,
                $command, INFLUX_FIELDSEPERATOR,
                $perfLabel, INFLUX_FIELDSEPERATOR,
                $name
            );
            if ($tag == 'normal') {
                $alias = $name;
            } else {
                $alias = $name.'-'.$tag;
            }
            $this->addTargetSimple(
                $target,
                $alias,
                array(array('key' => 'type', 'operator'  => '=', 'value' => $tag))
            );
            $this->addAliasColor($alias, $color);
        }
    }

    /**
    Adds yellow warning lines
    @param string $host      hostname.
    @param string $service   servicename.
    @param string $command   commandname.
    @param array  $perfLabel hostname.
    @return null.
    **/
    public function addWarning($host, $service, $command, $perfLabel)
    {
        $this->_addThreshold(
            $host, $service, $command, $perfLabel, 'warn', '#FFFF00'
        );
    }

    /**
    Adds red critical lines
    @param string $host      hostname.
    @param string $service   servicename.
    @param string $command   commandname.
    @param array  $perfLabel hostname.
    @return null.
    **/
    public function addCritical($host, $service, $command, $perfLabel)
    {
        $this->_addThreshold(
            $host, $service, $command, $perfLabel, 'crit', '#FF0000'
        );
    }

    /**
    Setter for Linewidth
    @param int $width Linewidth.
    @return null.
    **/
    public function setLinewidth($width)
    {
        $this->data['linewidth'] = $width;
    }
}
