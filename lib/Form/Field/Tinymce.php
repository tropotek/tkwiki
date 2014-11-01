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
class Form_Field_Tinymce extends Form_Field_Textarea
{
    
    /**
     * @var Js_Ui_TinyMce
     */
    protected $tinymce  = null;
    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param Form_Type $type
     * @return Form_Field_Tinymce
     */
    static function create($name, $type = null)
    {
        $obj = new self($name);
        $obj->tinymce = new Js_Ui_TinyMce(Js_Ui_TinyMce::MODE_NORMAL);
        $obj->getTinyMce()->disablePlugin('jdkmanager');  // disable manager by default
        $obj->setWidth(600);
        $obj->setHeight(400);

        $obj->addParam('browser_spellcheck', 'true');
        $obj->addParam('spellcheck', 'true');
        return $obj;
    }
    
    /**
     * Get the tinymce renderer
     *
     * @return Js_Ui_TinyMce
     */
    function getTinyMce()
    {
        return $this->tinymce;
    }
    
    
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
        parent::show($t);
        $this->tinymce->show($t);
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
  <textarea name="" var="element" style="width: 700px; height: 400px;" class="mceEditor"></textarea>
  <small var="notes" choice="notes"></small>
  <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
</div>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
}
