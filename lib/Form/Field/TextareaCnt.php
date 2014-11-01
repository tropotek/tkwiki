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
class Form_Field_TextareaCnt extends Form_Field
{
    /**
     * @var integer
     */
    protected $maxChar = 500;
    
    
    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param Form_Type $type
     * @param integer $maxChar
     * @return Form_Field_Textarea
     */
    static function create($name, $type = null, $maxChar = 500)
    {
        $obj = new self($name, $type);
        $obj->maxChar = $maxChar;
        return $obj;
    }
    
    /**
     * Render the default attributes of an element
     * @param Dom_Template $t
     */
    function showElement($t)
    {
        parent::showElement($t);
        
        $elId = $this->getElementId() . '-maxChar';
        $t->setAttr('maxBox', 'id', $elId);
        
        $js = <<<JS
$(document).ready(function() {
  $('#{$this->getElementId()}').keyup(function () {
    limitChars('{$this->getElementId()}', {$this->maxChar}, '$elId');
  });
});
JS;
        $t->appendJs($js);
        
        $t->insertText('element', $this->getSubFieldValue($this->name));
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
  <textarea name="" id="" class="textarea" var="element"></textarea><span var="maxBox"></span>
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
}