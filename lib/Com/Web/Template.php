<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A PHP5 DOM Template Library (extension for MVC components)
 *
 * @package Com
 */
class Com_Web_Template extends Dom_Template
{
    
    /**
     * component meata data
     * @var array
     */
    protected $componentList = array();
    
    
    
    

    /**
     * Make a template from a file
     *
     * @param string $filename
     * @param string $encoding
     * @return Dom_Template
     */
    static function loadFile($filename, $encoding = 'utf-8')
    {
        self::$capture = array('module', '@com-class');
        if (!is_file($filename)) {
            throw new RuntimeException('Cannot locate XML/XHTML file: ' . $filename);
        }
        $html = file_get_contents($filename);

        $obj = self::load($html, $encoding);
        $obj->document->documentURI = $filename;
        $obj->preInit();
        return $obj;
    }

    /**
     * Make a template from a string
     *
     * @param string $html
     * @param string $encoding
     * @return Dom_Template
     */
    static function load($html, $encoding = 'utf-8')
    {
        self::$capture = array('@com-class');
        if ($html == '' || $html[0] != '<') {
            throw new RuntimeException('Please supply a valid XHTML/XML string to create the DOMDocument.');
        }
        $isHtml5 = false;
        if ('<!doctype html>' == strtolower(substr($html, 0, 15))) {
            $isHtml5 = true;
            $html = substr($html, 16);
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        if (!$doc->loadXML(self::cleanXml($html, $encoding))) {
            $str = 'DOM_Template Read Error: ';
            foreach (libxml_get_errors() as $error) {
                $str .= sprintf("\n[%s:%s] %s", $error->line, $error->column, trim($error->message));
            }
            libxml_clear_errors();
            throw new RuntimeException($str);
        }
        $obj = new self($doc, $encoding);
        $obj->isHtml5 = $isHtml5;
        $obj->preInit();
        return $obj;
    }
    
    
    /**
     * This function is used when subclassing the template
     * implement this function to capture new nodes.
     * For this class we are capturing component meta data
     *
     * @param string $form The form name if inside a form.
     */
    protected function preInit()
    {
        $list = $this->getCaptureList();
        
        foreach ($list as $capName => $arr) {
            foreach ($arr as $node) {
                $com = new Com_Web_MetaData($node);
                $this->componentList[] = $com;
                if ($com->getInlineDom() != null) {
                    $this->removeChildren($node);
                }
            }
        }
    }
    
    /**
     * Get this templates widget array of Com_Web_MetaData objects
     *
     * @return array
     */
    function getComponentList()
    {
        return $this->componentList;
    }

}