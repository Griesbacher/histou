<?php
/**
Contains Folder Class.
PHP version 5
@category Folder_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
require_once 'histou/template.php';
/**
Folder Class.
PHP version 5
@category Folder_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class Folder
{

    /**
    Reads a list of directory and returns a list of Templates.
    @param array $folders list of folder strings.
    @return array of templates.
    **/
    public static function loadFolders($folders)
    {
        $templates = array();
        $alreadyRead = array();
        foreach ($folders as $folder) {
            static::_pushFolder($templates, $folder, $alreadyRead);
        }
        return $templates;
    }
    /**
    Reads each directory and pushes the template to the given list.
    @param array  $templates   list of templates.
    @param string $foldername  foldername.
    @param array  $alreadyRead list of known templates.
    @return null.
    **/
    private static function _pushFolder(&$templates, $foldername, &$alreadyRead)
    {
        if ($handle = opendir($foldername)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "."
                    && $file != ".."
                    && !in_array($file, $alreadyRead)
                ) {
                    if (static::_endsWith($file, '.php')) {
                        array_push(
                            $templates, static::_loadPHPTemplates($foldername.$file)
                        );
                        array_push($alreadyRead, $file);
                    } elseif (static::_endsWith($file, '.simple')) {
                        array_push(
                            $templates, static::_loadSimpleTemplates(
                                $foldername.$file
                            )
                        );
                        array_push($alreadyRead, $file);
                    }
                }
            }
            closedir($handle);
        }
    }

    /**
    Creates a Basic Template.
    @param string $filename foldername.
    @return object.
    **/
    private static function _loadPHPTemplates($filename)
    {
        include $filename;
        return new Template($filename, $rule, $genTemplate);
    }

    /**
    Creates a Simple Template.
    @param string $filename foldername.
    @return object.
    **/
    private static function _loadSimpleTemplates($filename)
    {
        return new SimpleTemplate($filename);
    }
    /**
    Tests if a string ends with a given string
    @param string $stringToSearch string to search in.
    @param string $extension      string to search for.
    @return object.
    **/
    private static function _endsWith($stringToSearch, $extension)
    {
        return $extension === "" ||
        (
        ($temp = strlen($stringToSearch) - strlen($extension)) >= 0
        && strpos($stringToSearch, $extension, $temp) !== false);
    }
}
