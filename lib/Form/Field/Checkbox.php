<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *  A form checkbox field object, usefull for boolean queries
 *
 * @package Form
 */
class Form_Field_Checkbox extends Form_Field
{

    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param Form_Type $type
     * @return Form_Field
     */
    static function create($name, $type = null)
    {
        $obj = new self($name, $type);
        $obj->setType(Form_Type_Boolean::create());
        return $obj;
    }
    
    /**
     * Is the value checked
     *
     * @return boolean
     */
    function isChecked()
    {
        if (isset($this->subFieldValues[$this->name])) {
            return ($this->subFieldValues[$this->name] == $this->name);
        }
        return false;
    }
    
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
        $this->showDefault($t);
        $this->showElement($t);
        if ($this->isChecked()) {
            $t->setAttr('element', 'checked', 'checked');
        }
    }
    
    
    /**
     * Render the default attributes of an element
     * @param Dom_Template $t
     */
    function showElement($t)
    {
        parent::showElement($t);
        $t->setAttr('element', 'value', $this->name);
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
  <input type="checkbox" name="" id="" class="inputCheckbox" var="element" />
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
    
}