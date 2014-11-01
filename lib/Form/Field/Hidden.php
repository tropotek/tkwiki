<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *  A form text field object
 *
 * @package Form
 */
class Form_Field_Hidden extends Form_Field
{
    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param Form_Type $type
     * @return Form_Field_Hidden
     */
    static function create($name, $type = null)
    {
        $obj = new self($name, $type);
        return $obj;
    }

    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
        //$this->showDefault($t);
        $this->showElement($t);
    }
    
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = sprintf('<?xml version="1.0"?>
<input type="hidden" var="element" name="" value="" />

');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
    
}