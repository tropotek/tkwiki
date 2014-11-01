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
class Form_Field_Date extends Form_Field
{
    
    

    /**
     * Create an instance of this object
     *
     * @param string $name
     * @return Form_Field
     */
    static function create($name)
    {
        $obj = new self($name, Form_Type_Date::create());
        $obj->setValue(Tk_Type_Date::create());
        $obj->setWidth(80);
        return $obj;
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
        $icon = Tk_Type_Url::create('/lib/Form/media/dateIcon.png')->toString();
        $js = <<<JS
$(function()
{
    $('#fid-{$this->getName()}').datepicker({ 
      dateFormat: 'dd/mm/yy',
      showOn: 'both',
      buttonImage: '$icon',
      buttonImageOnly: true
    }).css('width', '80px');
});
JS;
        $t->appendJs($js);
        
        
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
  <input type="text" name="" id="" class="inputText admDate" var="element" />
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
    
}