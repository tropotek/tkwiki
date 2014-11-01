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
class Form_Field_DateTime extends Form_Field
{
    
    

    /**
     * Create an instance of this object
     *
     * @param string $name
     * @return Form_Field
     */
    static function create($name)
    {
        $obj = new self($name, Form_Type_DateTime::create());
        $obj->setValue(Tk_Type_Date::create());
        $obj->setWidth(120);
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
        $t->appendJsUrl(Tk_Type_Url::create('/lib/Js/jquery/plugins/timepicker.js'));
        $icon = Tk_Type_Url::create('/lib/Form/media/dateIcon.png')->toString();
        $js = <<<JS
$(function()
{
    $('#fid-{$this->getName()}').datetimepicker({ 
      dateFormat: 'dd/mm/yy',
      showTime: true,
      timeFormat: 'hh:mm',
	//timeFormat: 'hh:mm:ss:l',	S
      stepHour: 1,
	  stepMinute: 5,
      hourGrid: 4,
	  minuteGrid: 15,
    //showSecond: true,
	//stepSecond: 10,
	//showMillisec: true,
    
      showOn: 'both',
      buttonImage: '$icon',
      buttonImageOnly: true
    }).css('width', '120px');
});
JS;
        $t->appendJs($js);
        
        $css = <<<CSS
/* css for timepicker */
.ui-timepicker-div .ui-widget-header{ margin-bottom: 8px; }
.ui-timepicker-div dl{ text-align: left; }
.ui-timepicker-div dl dt{ height: 25px; }
.ui-timepicker-div dl dd{ margin: -25px 0 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
CSS;
        $t->appendCss($css);
        
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