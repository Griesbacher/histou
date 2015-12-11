<?php
/**
Contains types of Panels.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\grafana;

/**
Base Panel.
PHP version 5
@category Panel_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
class TextPanel extends Panel
{
    const MARKDOWN = 'markdown';
    const TEXT = 'text';
    const HTML = 'html';

    /**
    Constructor.
    @param string $title name of the panel.
    @return object.
    **/
    public function __construct($title)
    {
        parent::__construct($title, 'text');
    }

    /**
    Setter for Mode
    @param int $mode Markdown,text,html.
    @return null.
    **/
    public function setMode($mode)
    {
        $this->data['mode'] = $mode;
    }

    /**
    Setter for Content
    @param int $content content.
    @return null.
    **/
    public function setContent($content)
    {
        $this->data['content'] = $content;
    }
}
