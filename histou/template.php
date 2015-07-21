<?php
trait enableLambdas
{
    public function __call($name, $args)
    {
        return call_user_func_array($this->$name, $args);
    }
}

class Template
{
    use enableLambdas;
    private $_file;
    private $_rule;
    private $_genTemplate;
        
    public function __construct($file, Rule $rule, closure $genTemplate) 
    {
        $this->_file = $file;
        $this->_rule = $rule;
        $this->_genTemplate = $genTemplate;
    }
    
    public function getPath()
    {
        return dirname($this->_file);
    }
    
    public function getFileName()
    {
        return basename($this->_file);
    }
    
    public function getTemplate($perfData)
    {
        return $this->_genTemplate($perfData);
    }
    
    public function __toString()
    {
        return "File:\t".$this->_file."\nRule: ".$this->_rule;
    }
    
    
    public static function compare($first, $second)
    {
        return Rule::compare($first->_rule, $second->_rule);
    }
    
    public function isValid()
    {
        return $this->_rule->isValid() == 0 ? true : false;
    }
    
    public static function findDefaultTemplate($templates,$defaultName)
    {
        foreach (array_reverse($templates) as $template) {
            if ($template->getFileName() == $defaultName) {
                return $template;
            }
        }
    }
}
?>
