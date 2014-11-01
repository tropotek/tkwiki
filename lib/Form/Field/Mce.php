<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A form text field object
 * 
 * @package Form
 */
class Form_Field_Mce extends Form_Field_Textarea
{
    
    /**
     * @var Js_Mce
     */
    protected $mce  = null;
    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param Js_Mce $mce
     * @return Form_Field_Tinymce
     */
    static function create($name, $mce = null)
    {
        $obj = new self($name);
        $obj->mce = $mce;
        if (!$obj->mce) {
            $obj->mce = Js_Mce::createNormal();
        }
        $obj->mce->setSelector('#fid-' . $name);
        $obj->setWidth(600);
        $obj->setHeight(400);
        $obj->mce->addParam('browser_spellcheck', 'true');
        $obj->mce->addParam('spellcheck', 'true');
        

        return $obj;
    }
    
    /**
     * Get the tinymce renderer
     *
     * @return Js_Mce
     */
    function getMce()
    {
        return $this->mce;
    }
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
        parent::show($t);
        
        $this->mce->setTemplate($t);
        $this->mce->show();
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
