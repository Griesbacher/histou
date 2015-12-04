<?php
/**
Contains a cache for templates.
PHP version 5
@category Template_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

require_once 'histou/templateLoader.php';
require_once 'histou/debug.php';

define("CACHE_FILE", '.histou_cache');
define("FILE_AGE_KEY", 'fileAge');
define("RULE_KEY", 'template');

/**
TemplateCache Class.
PHP version 5
@category TemplateCache_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

class TemplateCache
{
    private $_templates;
    private $_cachefile;

    /**
    Creates an cache.
    **/
    public function __construct()
    {
        $this->_cachefile = join(DIRECTORY_SEPARATOR, array(TMP_FOLDER, CACHE_FILE));
        if (file_exists($this->_cachefile)) {
            $this->_loadCache();
        } else {
            $this->_templates = array();
        }
    }

    /**
    Returns a list of templates.
    @param array $templatepaths list of requested templates.
    @return array list of templates.
    **/
    public function loadTemplates(array $templatepaths)
    {
        $templates = array();
        $hasChanged = false;
        foreach ($templatepaths as $path) {
            $fileAge = filemtime($path);
            if ($this->_templates != null
                && array_key_exists($path, $this->_templates)
                && $this->_templates[$path][FILE_AGE_KEY] >= $fileAge
            ) {
                array_push($templates, $this->_templates[$path][RULE_KEY]);
                continue;
            }
            $template = TemplateLoader::loadTemplate($path);
            if ($template == null) {
                Debug::add("The template: $path is not valid PHP!");
            } else {
                $hasChanged = true;
                $this->_templates[$path][FILE_AGE_KEY] = $fileAge;
                $this->_templates[$path][RULE_KEY] = $template->getRule();
                array_push($templates, $template);
            }
        }
        if ($hasChanged) {
            $this->_saveCache();
        }
        return $templates;
    }

    /**
    Loads the rules from the cachefile.
    @return null.
    **/
    private function _loadCache()
    {
        $this->_templates = unserialize(file_get_contents($this->_cachefile));
    }

    /**
    Saves the rules to the cachefile.
    @return null.
    **/
    private function _saveCache()
    {
        file_put_contents($this->_cachefile, serialize($this->_templates));
    }
}
