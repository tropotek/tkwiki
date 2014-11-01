<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Use this to create a component for dynamic pages
 *
 * @package Com
 */
class Com_Web_PageData extends Com_Web_MetaData
{
    const VAR_DYNAMIC_CONTENT = '_Module_Content_';
    
    /**
     * @var string
     */
    protected $templatePath = '';
    
    /**
     * Create a metaData object from an admin page params array
     *
     * @param string $class The component classname
     * @param string $template The template path. Default: '/index.html'
     * @param array $params These are the params prepended with 'param-' to set elements of a component
     * @return Com_Web_PageData
     */
    static function createPageData($class = '', $templatePath = '', $params = array())
    {
        $obj = new self();
        $obj->classname = $class;
        if ($templatePath && $templatePath[0] != '/' && $templatePath[0] != '\\') {
                $templatePath = '/' . $templatePath;
        }
        foreach ($params as $key => $val) {
            if (substr($key, 0, 6) == 'param-') {
                $obj->parameters[substr($key, 6)] = $val;
            } else {
                $obj->parameters[$key] = $val;
            }
        }
        if (!$templatePath) {
            $templatePath = '/index.tpl';
        }
        $obj->templatePath = $templatePath;
        $obj->insertVar = self::VAR_DYNAMIC_CONTENT;
        return $obj;
    }
    
    /**
     * Used for dynamic pages
     * Return the location of this dynamic page template
     *
     * @return string
     */
    function getTemplatePath()
    {
        return $this->templatePath;
    }

}