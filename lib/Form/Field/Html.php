<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *  A form Html field object
 *  Use this if you require a readonly field, usefull for displaying field data
 *
 * @package Form
 */
class Form_Field_Html extends Form_Field
{
    /**
     * @var boolean
     */
    private $showLabel = true;
    
    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param string $value
     * @return Form_Field_Text
     */
    static function create($name, $value)
    {
        $obj = new self($name);
        $obj->setRawValue($value);
        $obj->setLoadable(false);
        return $obj;
    }
    
    /**
     * Set the label status
     *
     * @param boolean $b
     * @return Form_Field_Html
     */
    function showLabel($b)
    {
        $this->showLabel = $b;
        return $this;
    }
    
    
    /**
     * Set the value of the element from a mixed type
     *
     * @param mixed $value
     * @return Form_Field
     */
    function setValue($value)
    {
        return $this;
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
        $t->insertHtml('element', $this->getValue());
        if (!$this->showLabel) {
            $t->insertHtml('label', '&#160;');
        }
    }
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = sprintf('<?xml version="1.0"?>
<div class="field html" var="block">
  <p class="error" var="error" choice="error"></p>
  <label for="fid-code" var="label"></label>
  <div var="element" class="eContent"></div>
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
    
}