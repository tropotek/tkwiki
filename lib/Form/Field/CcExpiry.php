<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Displays a field for the credit card expiry input
 *
 *
 * @package Form
 */
class Form_Field_CcExpiry extends Form_Field
{
/**
     * Create an instance of this object
     *
     * @param string $name
     * @param Form_Type $type
     * @return Form_Field_CcExpiry
     */
    static function create($name, $type = null)
    {
        $obj = new self($name, $type);
        return $obj;
    }

    /**
     * Render the default attributes of an element
     * @param Dom_Template $t
     */
    function showElement($t)
    {
        if (!$t->keyExists('var', 'element')) {
            return;
        }
        if (!$this->enabled) {
            $t->setAttr('element', 'disabled', 'disabled');
        }
        if ($this->readonly) {
            $t->setAttr('element', 'readonly', 'readonly');
        }
        if (!$this->autocomplete) {
            $t->setAttr('element', 'autocomplete', 'off');
        }
        if ($this->accessKey) {
            $t->setAttr('element', 'accesskey', $this->accessKey);
        }
        if ($this->tabindex > 0) {
            $t->setAttr('element', 'tabindex', $this->tabindex);
        }
        if ($this->width > 0 && !isset($this->styleList['width'])) {
            $this->addStyle('width', $this->width . 'px');
        }
        if ($this->height > 0 && !isset($this->styleList['height'])) {
            $this->addStyle('height', $this->height . 'px');
        }
        foreach ($this->attrList as $attr => $js) {
            $t->setAttr('element', $attr, $js);
        }
        $styleStr = '';
        foreach ($this->styleList as $style => $val) {
            $styleStr .= $style . ': ' . $val . '; ';
        }
        if ($styleStr) {
            $t->setAttr('element', 'style', $styleStr);
        }
        
        
        // Element
        //$t->setAttr('element', 'name', $this->name);
        //$t->setAttr('element', 'id', $this->getElementId());
//        if ($t->getVarElement('element')->nodeName == 'input') {
//            if ($this->maxlength > 0) {
//                $t->setAttr('element', 'maxlength', $this->maxlength);
//            }
//            if ($this->value !== null && !is_array($this->getSubFieldValue($this->name))) {
//                $t->setAttr('element', 'value', $this->getSubFieldValue($this->name));
//            }
//        }
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
  
  <input type="text" name="ccExpMonth" id="fid-ccExpMonth" class="inputText ccExp" value="mm" var="element" onfocus="if (this.value == \'mm\') {this.value=\'\';this.style.color = \'#000\';}"/> /
  <input type="text" name="ccExpYear" id="fid-ccExpYear" class="inputText ccExp" value="yy" var="element" onfocus="if (this.value == \'yy\') {this.value=\'\';this.style.color = \'#000\';}"/>
  
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
}