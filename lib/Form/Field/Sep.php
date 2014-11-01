<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *  A form field seperator
 *  This field does nothing but add a blank field to act as a seperator
 *
 * @package Form
 */
class Form_Field_Sep extends Form_Field
{

    /**
     * Create an instance of this object
     *
     * @return Form_Field_Sep
     */
    static function create()
    {
        $obj = new self('sep'.microtime(true));
        return $obj;
    }
    
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = sprintf('<?xml version="1.0"?>
<div class="field sep" var="block" style="display: inline-block;width: 100%%;padding: 5px 0;">
  
  <label>&#160;</label>
  
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
    
}