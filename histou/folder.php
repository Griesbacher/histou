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

require_once 'histou/templateLoader.php';

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
    @return array of templateFiles.
    **/
    public static function loadFolders($folders)
    {
        $templateFiles = array();
        $alreadyRead = array();
        foreach ($folders as $folder) {
            static::_pushFolder($templateFiles, $folder, $alreadyRead);
        }
        return $templateFiles;
    }

    /**
    Reads each directory and pushes the template to the given list.
    @param array  $templateFiles list of templateFiles.
    @param string $foldername    foldername.
    @param array  $alreadyRead   list of known templateFiles.
    @return null.
    **/
    private static function _pushFolder(&$templateFiles, $foldername, &$alreadyRead)
    {
        if ($handle = opendir($foldername)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "."
                    && $file != ".."
                    && !in_array($file, $alreadyRead)
                ) {
                    array_push($templateFiles, join(DIRECTORY_SEPARATOR, array($foldername,$file)));
                    array_push($alreadyRead, $file);
                }
            }
            closedir($handle);
        }
    }

    /**
    Returns true if the fileending is a valid one.
    @param string $filename path or filename.
    @return bool true if it ends with '.simple' or '.php'.
    **/
    private static function _isValidFile($filename)
    {
        return TemplateLoader::endswith($filename, '.simple') || TemplateLoader::endswith($filename, '.php');
    }
}