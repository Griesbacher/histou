<?php
/**
Contains Folder Class.
PHP version 5
@category Folder_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou;

/**
Folder Class.
PHP version 5
@category Folder_Class
@package histou
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
            static::pushFolder($templateFiles, $folder, $alreadyRead);
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
    private static function pushFolder(&$templateFiles, $foldername, &$alreadyRead)
    {
        try {
            if ($handle = opendir($foldername)) {
                while (false !== ($file = readdir($handle))) {
                    if (!\histou\helper\Str::startsWith($file, '.')
                        && !in_array($file, $alreadyRead)
                        && static::isValidFile($file)
                    ) {
                        array_push($templateFiles, join(DIRECTORY_SEPARATOR, array($foldername,$file)));
                        array_push($alreadyRead, $file);
                    }
                }
                closedir($handle);
            }
        } catch (\ErrorException $e) {
            \histou\Debug::add("Could not open folder: $foldername");
        }
    }

    /**
    Returns true if the fileending is a valid one.
    @param string $filename path or filename.
    @return bool true if it ends with '.simple' or '.php'.
    **/
    private static function isValidFile($filename)
    {
        return \histou\helper\Str::endswith($filename, '.simple') || \histou\helper\Str::endswith($filename, '.php');
    }
}
