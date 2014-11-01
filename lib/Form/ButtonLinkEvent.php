<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *
 *
 * @package Form
 */
class Form_ButtonLinkEvent extends Form_ButtonEvent
{
    
    
    /**
     * Render the default attributes of an element
     * @param Dom_Template $t
     */
    function showElement($t)
    {
        if (!$t->keyExists('var', 'element')) {
            return;
        }
        $url = Tk_Type_Url::create();
        
        $t->insertText('element', $this->getLabel());
        
        $t->setAttr('element', 'href', $url);
        $t->setAttr('element', 'title', $this->getName());
        //$t->setAttr('element', 'id', $this->getElementId());
        
        $js = "$(window).unbind('beforeunload');submitForm(document.getElementById('{$this->form->getFormId()}'), '{$this->getName()}');return false;";
        $t->setAttr('element', 'onclick', $js);
        
        if (!$this->enabled) {
            $t->setAttr('element', 'title', 'disabled');
            $t->setAttr('element', 'onclick', 'return false;');
        }
        if ($this->tabindex > 0) {
            $t->setAttr('element', 'tabindex', $this->tabindex);
        }
        foreach ($this->attrList as $attr => $js) {
            $t->setAttr('element', $attr, $js);
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
<a href="#" var="element"></a>
');
        $template = Dom_Template::load($xmlStr);
        $template->appendJsUrl(Tk_Type_Url::create('/lib/Js/Util.js'));
        return $template;
    }
    
    
}