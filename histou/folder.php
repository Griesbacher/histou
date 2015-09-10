<?php
require_once 'histou/template.php';
class Folder
{

    public static function loadFolders($folders)
    {
        $templates = array();
        $alreadyRead = array();
        foreach ($folders as $folder) {
            static::_pushFolder($templates, $folder, $alreadyRead);
        }
        return $templates;
    }

    private static function _pushFolder(&$templates, $foldername, &$alreadyRead)
    {
        if ($handle = opendir($foldername)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && !in_array($file, $alreadyRead)) {
                    if (static::_endsWith($file, '.php')) {
                        array_push($templates, static::_loadPHPTemplates($foldername.$file));
                        array_push($alreadyRead, $file);
                    }elseif (static::_endsWith($file, '.simple')) {
                        array_push($templates, static::_loadSimpleTemplates($foldername.$file));
                        array_push($alreadyRead, $file);
                    }
                }
            }
            closedir($handle);
        }
    }

    private static function _loadPHPTemplates($filename)
    {
        include $filename;
        return new Template($filename, $rule, $genTemplate);
    }

    private static function _loadSimpleTemplates($filename)
    {
        return new SimpleTemplate($filename);
    }

    private static function _endsWith($haystack, $needle)
    {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}
?>
