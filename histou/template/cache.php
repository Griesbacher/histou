<?php
/**
Contains a cache for templates.
PHP version 5
@category Template_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

namespace histou\template;

define("CACHE_FILE", '.histou_cache');
define("FILE_AGE_KEY", 'fileAge');
define("RULE_KEY", 'template');

/**
Cache Class.
PHP version 5
@category Cache_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

class Cache
{
    private $templates;
    private $cachefile;

    /**
    Creates an cache.
    **/
    public function __construct()
    {
        $this->cachefile = join(DIRECTORY_SEPARATOR, array(TMP_FOLDER, CACHE_FILE));
        if (file_exists($this->cachefile)) {
            $this->loadCache();
        } else {
            $this->templates = array();
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
            if ($this->templates != null
                && array_key_exists($path, $this->templates)
                && $this->templates[$path][FILE_AGE_KEY] >= $fileAge
            ) {
                array_push($templates, $this->templates[$path][RULE_KEY]);
                continue;
            }
            $template = \histou\template\loader::loadTemplate($path);
            if ($template == null) {
                Debug::add("The template: $path is not valid PHP!");
            } else {
                $hasChanged = true;
                $this->templates[$path][FILE_AGE_KEY] = $fileAge;
                $this->templates[$path][RULE_KEY] = $template->getRule();
                array_push($templates, $template);
            }
        }
        if ($hasChanged) {
            $this->saveCache();
        }
        return $templates;
    }

    /**
    Loads the rules from the cachefile.
    @return null.
    **/
    private function loadCache()
    {
        $this->templates = unserialize(file_get_contents($this->cachefile));
    }

    /**
    Saves the rules to the cachefile.
    @return null.
    **/
    private function saveCache()
    {
        file_put_contents($this->cachefile, serialize($this->templates));
    }
}
