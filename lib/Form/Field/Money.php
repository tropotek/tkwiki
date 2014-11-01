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
class Form_Field_Money extends Form_Field
{

    /**
     * Create an instance of this object
     *
     * @param string $name
     * @return Form_Field_Money
     */
    static function create($name)
    {
        $obj = new self($name, Form_Type_Money::create());
        $obj->setWidth(70);
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
<div class="field" var="block">
  <p class="error" var="error" choice="error"></p>
  <label for="fid-code" var="label"></label>
  <input type="text" name="" id="" class="inputText admMoney" var="element"/>
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
    
}