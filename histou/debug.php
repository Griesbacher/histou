<?php
class Debug
{
    private static $_enabled = false;
    private static $_log = array();
    private static $_booleanArray = Array(false => 'false', true => 'true');
    
    
    public static function isEnable()
    {
        return static::$_enabled;
    }
    
    public static function enable()
    {
        static::$_enabled = true;
    }

    public static function add($line)
    {
        array_push(static::$_log, $line."\n");
    }

    public static function getLogAsMarkdown()
    {
        $output = '';
        foreach (static::$_log as $line) {
            
            $output .= sprintf("####%s", substr(str_replace("\n", "\n####", $line), 0, -4));            
        }
        return $output;
    }

    public static function genMarkdownRow($message, $header = '')
    {
        $panel = new TextPanel('');
        $panel->setMode(TextPanel::MARKDOWN);
        $panel->setContent($message);
        $row = new Row($header);
        $row->addPanel($panel);
        return $row;
    }
    
    public static function errorMarkdownDashboard($message)
    {
        $dashboard = new Dashboard('Error');
        $dashboard->addRow(static::genMarkdownRow($message, 'ERROR'));
        return $dashboard;        
    }
    public static function printBoolean($bool)
    {        
        return static::$_booleanArray[$bool];
    }
}
?>
