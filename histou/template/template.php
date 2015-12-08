<?php
/**
Contains types of Templates.
PHP version 5
@category Template_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\template;

/**
Base Class Template.
PHP version 5
@category Template_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class Template
{
    use Lambda;
    private $file;
    private $rule;
    private $genTemplate;

    /**
    Creates a template.
    @param string  $file        Path to the templatefile.
    @param object  $rule        ruleset.
    @param closure $genTemplate lambda to create dashboard.
    @return object.
    **/
    public function __construct($file, Rule $rule, \closure $genTemplate)
    {
        $this->file = $file;
        $rule->file = $file;
        $this->rule = $rule;
        $this->genTemplate = $genTemplate;
    }

    /**
    Returns the rule of the template.
    @return rule.
    **/
    public function getRule()
    {
        return $this->rule;
    }

    /**
    Returns the path to the template.
    @return string.
    **/
    public function getPath()
    {
        return dirname($this->file);
    }

    /**
    Returns the basename of the file.
    @return string.
    **/
    public function getBaseName()
    {
        return basename($this->file);
    }

    /**
    Returns the Filename, without extension.
    @return string.
    **/
    public function getSimpleFileName()
    {
        return strstr($this->getBaseName(), '.', true);
    }

    /**
    Generates a dashboard from the template and returns the dashboard.
    @param array $perfData array of performance data from influxdb.
    @return object.
    **/
    public function generateDashboard($perfData)
    {
        return $this->genTemplate($perfData);
    }

    /**
    Prints the Template in a nice format.
    @return string.
    **/
    public function __toString()
    {
        return "File:\t".$this->file."\nRule: ".$this->rule;
    }

    /**
    Tests if a Template matches the given Tablename.
    @param string $tablename Name to test.
    @return boolean.
    **/
    public function matchesTablename($tablename)
    {
        return $this->rule->matchesTablename($tablename);
    }

    /**
    Sort function to order the objects
    @param object $first  Template1.
    @param object $second Template2.
    @return int.
    **/
    public static function compare($first, $second)
    {
        return \histou\template\Rule::compare(static::getRuleFromX($first), static::getRuleFromX($second));
    }

    /**
    Returns a rule, if the object is a template the rule within will be returned.
    @param object $maybeRule object to test.
    @return rule.
    **/
    private static function getRuleFromX($maybeRule)
    {
        $className = get_class($maybeRule);
        if ($className == 'histou\template\Rule') {
            return $maybeRule;
        } elseif ($className == 'histou\template\Template' || $className == 'histou\template\SimpleTemplate') {
            return $maybeRule->rule;
        } else {
            throw new \Exception("unkown class $className");
        }
    }

    /**
    Test if the rule of the template is valid.
    @return boolean.
    **/
    public function isValid()
    {
        return $this->rule->isValid();
    }

    /**
    Searches for the given default template in the template list.
    @param array  $templates   List of templates.
    @param string $defaultName Filename.
    @return object.
    **/
    public static function findDefaultTemplate($templates, $defaultName)
    {
        foreach (array_reverse($templates) as $template) {
            if ($template->getBaseName() == $defaultName) {
                return $template;
            }
        }
    }
}
