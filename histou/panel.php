<?php
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
    function __construct($title, $type)
    {
        $this->data['title'] = $title;
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

    function __construct($title)
    {
        parent::__construct($title, 'text');
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
                                "query" => sprintf('select mean(value) from "%s" where AND $timeFilter group by time($interval)', $target),
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

    private function addThreshold($host, $service, $command, $perfLable, $name, $color)
    {
        foreach(array('normal', 'min', 'max') as $tag){
            $target = sprintf('%s%s%s%s%s%s%s%s%s', $host, INFLUX_FIELDSEPERATOR, $service, INFLUX_FIELDSEPERATOR, $command, INFLUX_FIELDSEPERATOR, $perfLable, INFLUX_FIELDSEPERATOR, $name);
            if($tag == 'normal'){
                $alias = $name;
            }else{
                $alias = $name.'-'.$tag;
            }
            $this->addTargetSimple($target, $alias, array(array('key' => 'type', 'operator'  => '=', 'value' => $tag)));
            $this->addAliasColor($alias, $color);
        }
    }

    public function addWarning($host, $service, $command, $perfLable)
    {
        $this->addThreshold($host, $service, $command, $perfLable, 'warn', '#FFFF00');
    }

    public function addCritical($host, $service, $command, $perfLable)
    {
        $this->addThreshold($host, $service, $command, $perfLable, 'crit', '#FF0000');
    }

    public function setLinewidth($width)
    {
        $this->data['linewidth'] = $width;
    }
}
?>
