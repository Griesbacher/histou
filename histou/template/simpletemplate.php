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
Inheritate from Template for simple Templatefiles
PHP version 5
@category Template_Class
@package histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class SimpleTemplate extends Template
{
    /**
    Expects a filename to the simple config.
    @param string $file Path to file.
    @return object.
    **/
    public function __construct($file)
    {
        $result = parser::parseSimpleTemplate($file);
        parent::__construct($file, $result[0], $result[1]);
    }
}
