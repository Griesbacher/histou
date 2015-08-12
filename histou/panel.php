<?php
abstract class Panel
{
    protected $data = array(
                        'titel' => null,
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
    function __construct($titel, $type)
    {
        $this->data['titel'] = $titel;
        $this->data['type'] = $type;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function setSpan($spanSize)
    {
        $this->data['span'] = $spanSize;
    }

    public function setEditable(boolean $editable)
    {
        $this->data['editable'] = $editable;
    }

    public function setId($id)
    {
        $this->data['id'] = $id;
    }
	public function setLinewidth($width)
    {
        $this->data['linewidth'] = $width;
    }
	public function setCustomProperty($name, $value)
    {
        $this->data[$name] = $value;
    }
	
}
class TextPanel extends Panel
{
    const MARKDOWN = 'markdown';
    const TEXT = 'text';
    const HTML = 'html';

    function __construct($titel)
    {
        parent::__construct($titel, 'text');
    }

    public function setMode($mode)
    {
        $this->data['mode'] = $mode;
    }

    public function setContent($content)
    {
        $this->data['content'] = $content;
    }
}

class GraphPanel extends Panel
{
    function __construct($titel)
    {
        parent::__construct($titel, 'graph');
        $this->data['tooltip'] = array(
                                'show' =>  false,
                                'values' =>  false,
                                'min' =>  false,
                                'max' =>  false,
                                'current' =>  false,
                                'total' =>  false,
                                'avg' =>  false,
                                'shared' =>  false
                            );
        $this->data['fill'] = 1;
        $this->data['linewidth'] = 2;
        $this->data['targets'] = array();
    }

    public function setTooltip(array $tooltip)
    {
        $this->data['tooltip'] = tooltip;
    }
	
	public function addTargetSimple($target, $alias = "", array $tags = array())
    {
        array_push(
            $this->data['targets'], array(
                                "function" => "mean",
                                "column" => "value",
                                "measurement" => sprintf($target),
                                "query" => sprintf('select mean(value) from "%s" where $timeFilter group by time($interval) order asc', $target),
								"alias" => $alias,
								"tags" => $tags
                                )
        );
    }
	
	public function addAliasColor($alias, $color)
    {
		if(!isset($this->data['aliasColors'])){
			$this->data['aliasColors'] = array();
		}
        $this->data['aliasColors'][$alias] = $color;
	}
	
	public function setleftYAxisLabel($label)
    {
		$this->data['leftYAxisLabel'] = $label;
		
	}
}
?>
