<?php
/**
Contains Debug Class.
PHP version 5
@category Folder_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
namespace histou;

/**
Debug Class.
PHP version 5
@category Folder_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/

class Debug
{
    private static $enabled = false;
    private static $log = array();
    private static $booleanArray = array(false => 'false', true => 'true');


    /**
    Returns if debug is enabled or not.
    @return boolean
    **/
    public static function isEnable()
    {
        return static::$enabled;
    }

    /**
    Enables the debug output.
    @return null
    **/
    public static function enable()
    {
        static::$enabled = true;
    }

    /**
    Adds a line to the output.
    @param string $line line to add to log.
    @return null
    **/
    public static function add($line)
    {
        array_push(static::$log, $line."\n");
    }

    /**
    Returns the log in markdownformat.
    @return string
    **/
    public static function getLogAsMarkdown()
    {
        $output = '';
        foreach (static::$log as $line) {
            $output .= sprintf(
                "#### %s",
                substr(str_replace("\n", "\n#### ", $line), 0, -5)
            );
        }
        return $output;
    }

    /**
    Returns the log.
    @return string
    **/
    public static function getLog()
    {
        return implode("\n", static::$log);
    }

    /**
    Creates a new Markdown row.
    @param string $message contains the body.
    @param string $header  could contain the headline.
    @return object
    **/
    public static function genRow($message, $mode = \histou\grafana\TextPanel::TEXT, $header = '')
    {
        $panel = new \histou\grafana\TextPanel('');
        $panel->setMode($mode);
        $panel->setContent($message);
        $row = new \histou\grafana\Row($header);
        $row->addPanel($panel);
        return $row;
    }

    /**
    Creates a new Markdown dashboard.
    @param string $message contains the body.
    @return object
    **/
    public static function errorMarkdownDashboard($message)
    {
        $dashboard = \histou\grafana\dashboard\DashboardFactory::generateDashboard('Error');
        $dashboard->addRow(static::genRow($message, \histou\grafana\TextPanel::MARKDOWN, 'ERROR'));
        return $dashboard;
    }

    /**
    Prints a boolean as string.
    @param boolean $bool boolean to print
    @return string
    **/
    public static function printBoolean($bool)
    {
        return static::$booleanArray[$bool];
    }
}
