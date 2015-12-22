<?php
/**
Loads Templates from files.
PHP version 5
@category Loader
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\template;

/**
Loads Templates from files.
PHP version 5
@category Loader
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class Loader
{
    /**
    Creates a Template from file.
    @param string $filename foldername.
    @param bool   $save     if true no syntax check will be done.
    @return object.
    **/
    public static function loadTemplate($filename, $save = false)
    {
        if (\histou\helper\Str::endswith($filename, '.php')) {
            return static::loadPHPTemplates($filename, $save);
        } elseif (\histou\helper\Str::endswith($filename, '.simple')) {
            return static::loadSimpleTemplates($filename);
        }
    }

    /**
    Creates a Basic Template.
    @param string $filename foldername.
    @param bool   $save     if true no syntax check will be done.
    @return object.
    **/
    private static function loadPHPTemplates($filename, $save)
    {
        if (!$save && !static::isFileValidPHP($filename)) {
            return null;
        }
        include $filename;
        return new Template($filename, $rule, $genTemplate);
    }

    /**
    Creates a Simple Template.
    @param string $filename foldername.
    @return object.
    **/
    private static function loadSimpleTemplates($filename)
    {
        return new SimpleTemplate($filename);
    }

    /**
    Uses the php -l command to test if a file contains valid PHP code.
    @param string $filePath path to the file to check.
    @return bool.
    **/
    public static function isFileValidPHP($filePath)
    {
        //TODO:test if php content. e.g. just foo would work...
        ob_start();
        system(PHP_COMMAND." -l $filePath 2>&1", $returnCode);
        $syntaxCheck = ob_get_contents();
        ob_end_clean();
        if (substr($syntaxCheck, 1, 12) == "Parse error:") {
            \histou\Debug::add("Syntaxcheck: ".$syntaxCheck);
        }
        return $returnCode == 0;
    }
}
