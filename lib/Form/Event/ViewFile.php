<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Form_Event_ViewFile
 *
 * @package Form
 * @todo: Time to update the view button, instead of a popup we should use
 *  a lightbox with a div in it.
 */
class Form_Event_ViewFile extends Form_ButtonEvent
{
    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @return Form_EventSave
     */
    static function create($name)
    {
        $evt = new self($name);
        $evt->setButtonType(self::TYPE_BUTTON);
        return $evt;
    }
    
    /**
     * Get the Url object to the file location
     * 
     * @param string $path The object file path
     * @return Tk_Type_Url
     */
    function getFileUrl($path)
    {
        return Tk_Type_Url::createDataUrl($path);
    }
    
    
    /**
     * Use this to do any initalisations after the form is set
     *
     * @param Form $form
     */
    function onSetForm($form)
    {
        $controller = $form->getEventController();
        $getMethod = 'get' . ucfirst(str_replace('view', '', $this->getName()));
        if (!$controller->getObject()->$getMethod()) {
            $this->setEnabled(false);
        }
        $url = $this->getFileUrl($controller->getObject()->$getMethod());
        
        if ($this->enabled) {
            $this->addAttr('title', ucfirst($this->getField()->getName()) . ' - ' . $url->getBasename());
            $this->addAttr('href', $url->toString());
            $this->addAttr('class', 'view lightbox');
        }
    }
    
    function show($t = null)
    {
        parent::show($t);
        
        $js = <<<JS
$(document).ready(function() {
  $('a.view').unbind('click');
});
JS;
        $t->appendJs($js);
        
        // Setup fancybox lightbox for images.
        Js_Ui_JqFancybox::create($t)->show();
    }
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = sprintf('<?xml version="1.0"?>
<a href="javascript: return false;" var="element" class="view" title="Disabled">View</a>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
    
}