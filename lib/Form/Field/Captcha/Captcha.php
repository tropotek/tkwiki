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
class Form_Field_Captcha extends Form_Field
{
    
    const SID = 'captcha_';

    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param Form_Type $type
     * @return Form_Field_Captcha
     */
    static function create($name, $type = null)
    {
        $obj = new self($name, $type);
        $obj->setWidth(60);
        $obj->setAutocomplete(false);
        $obj->addEvent(new Form_Handler_Capcha());
        return $obj;
    }

    /**
     *
     * @return string
     */
    function getSid()
    {
        return self::SID . '_' . $this->getForm()->getId();
    }
    
    /**
     * Set the form submit handler
     *
     * @param Form_Handler $handler
     * @return Form_Field
     */
    function setHandler(Form_Handler $handler)
    {
        Tk::log('Cannot add a handler to the captcha field.', Tk::LOG_ALERT);
        return $this;
    }
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
        parent::show($t);
        
        $url = Tk_Type_Url::create('/lib/Form/Field/Captcha/image.php');
        $url->set('id', $this->getForm()->getId());
        
        $t->setAttr('image', 'src', $url->toString());
        $t->setAttr('image', 'id', $this->getElementId() . '-img');
        
        $js = sprintf("$('#%s').attr('src', '%s&r='+(new Date().getTime()));", $this->getElementId() . '-img', $url->toString());
        $t->setAttr('reload', 'onclick', $js);
        
        if (!Tk_Session::exists($this->getSid())) {
            $id = $this->getElementId() . '-img';
            $js = <<<JS
$(document).ready(function() {
    $('#$id').attr('src', '{$url->toString()}&r='+(new Date().getTime()));
});
JS;

            $t->appendJs($js);
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
<div class="field captcha" var="block">
  <p class="error" var="error" choice="error"></p>
  <label for="fid-code" var="label">Code:</label>
  <input type="text" name="" id="" class="inputText" var="element" />
  <div class="extraBlock" style="vertical-align:top;">
    <img src="" var="image" id="" style="vertical-align: bottom;" alt="validate" title="Validation Image"/> <a href="#" class="reloadCapcha" var="reload">Reload</a>
  </div>
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
}


class Form_Handler_Capcha extends Form_Event
{
    
    function init()
    {
        $this->setTrigerList(array(Form_Event::TRIGER_ON_ALL));
    }
    
    
    /**
     * This will be called before the event's execute() method is called
     *
     */
    function preExecute()
    {
        $field = $this->getField();
        if (Tk_Session::get($field->getSid()) !== $field->getValue()) {
        	$field->addError('Please enter a valid code.');
        }
    }
    
    /**
     * This will be called after the event's execute() method is called
     *
     */
    function postExecute()
    {
        if (!$this->getForm()->hasErrors()) {
        	Tk_Session::delete($this->getField()->getSid());
        }
    }
}
