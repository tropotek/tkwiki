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
class Form_Field extends Form_Element
{
    
    /**
     * @var Form
     */
    protected $form = null;
    
    /**
     * @var string
     */
    protected $label = '';
    /**
     * @var boolean
     */
    protected $render = true;
    
    /**
     * @var boolean
     */
    protected $enabled = true;
    
    /**
     * @var string
     */
    protected $accessKey = '';
    
    /**
     * Tab indexes of <= 0 are ignored
     * @var integer
     */
    protected $tabindex = 0;
    
    /**
     * @var string
     */
    protected $attrList = array();
    
    /**
     * @var string
     */
    protected $styleList = array();
    
    /**
     * @var array
     */
    protected $help = '';
    
    /**
     * @var string
     */
    protected $notes = '';
    
    /**
     * @var string
     * @todo: implement javascript validation or similar
     */
    protected $js = '';
    
    /**
     * @var boolean
     */
    protected $autocomplete = true;
    
    /**
     * @var boolean
     */
    protected $required = false;
    
    /**
     * @var boolean
     */
    protected $readonly = false;
    
    /**
     * @var string
     */
    protected $tabGroup = '';
    
    /**
     * Submit event handler
     *
     * @var Form_Handler
     */
    protected $handler = null;
    
    /**
     * @var integer
     */
    protected $maxlength = 0;
    
    /**
     * @var integer
     */
    protected $width = 0;
    
    /**
     * @var integer
     */
    protected $height = 0;
    
    /**
     * @var array
     */
    protected $eventList = array();
    

    /**
     * __construct
     *
     * @param string $name
     * @param Form_Type $type
     * @param string $label
     */
    function __construct($name, $type = null)
    {
        parent::__construct($name, $type);
        // Set the default Label
        $this->label = ucfirst(preg_replace('/[A-Z]/', ' $0', $this->name));
        $this->label = preg_replace('/(\[\])/', '', $this->label);
        if (substr($this->label, -2) == 'Id') {
            $this->label = substr($this->label, 0, -3);
        }
    }
    
    /**
     * Get the object associated to the form
     * This may be an array or an object determined by params passed to
     * the form constructor
     *
     * @return Tk_Object or an array()
     * @see Form::__construct()
     */
    function getObject()
    {
        return $this->getForm()->getObject();
    }
    
    /**
     * Get the label of this element
     * @return string
     */
    function getLabel()
    {
        return $this->label;
    }
    
    /**
     * Set the label of this element
     *
     * @param $str
     * @return Form_Field
     */
    function setLabel($str)
    {
        $this->label = $str;
        return $this;
    }

    
    /**
     * Use this to do any initalisations after the form is set.
     * This can be handy if trying to do something to a field before the
     * form has been attached to it.
     *
     * @param Form $form
     * @todo: Check if this is required.
     * @deprecated
     */
    function onSetForm($form) {}
    
    /**
     * Add an event to the field
     * Events added before the form is set are queued for addition,
     *   once the setForm() method is called these events are added to the form
     *
     * @param Form_Event $event
     * @param string $eventName
     * @return Form_Field
     */
    function addEvent(Form_Event $event)
    {
        $event->bind($this);
        //$this->eventList[] = $event;
        array_unshift($this->eventList, $event);
        if ($this->form) {
            $this->form->addEvent($event);
        }
        return $this;
    }
    
    /**
     * Set the parent form object, this can only be set once.
     *
     * @param Form $form
     * @return Form_Field
     */
    function setForm($form)
    {
        if ($this->form instanceof Form) {
            return $this;
        }
        $this->form = $form;
        if (count($this->eventList)) {
            foreach ($this->eventList as $event) {
                $this->form->addEvent($event);
            }
        }
        $this->onSetForm($this->form);
        return $this;
    }
    
    /**
     * Get the parent form object
     *
     * @return Form
     */
    function getForm()
    {
        return $this->form;
    }
    
    /**
     * Set the render state of the field
     *
     * @param boolean $b
     * @return Form_Field
     */
    function setRender($b)
    {
        $this->render = ($b === true);
        return $this;
    }
    
    /**
     * Get the render status of the field
     *
     * @return boolean
     */
    function getRender()
    {
        return $this->render;
    }
    
    /**
     * Set the enabled state of this field
     *
     * @param boolean $b
     */
    function setEnabled($b)
    {
        $this->enabled = ($b == true);
    }
    
    /**
     * Is this element enabled
     *
     * @return boolean
     */
    function isEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * Set the access key
     * EG 'a' = ALT+a
     *
     * @param string $char
     */
    function setAccessKey($char)
    {
        $this->accessKey = substr($char, 0, 1);
    }
    
    /**
     * Set the tab index of this field
     *
     * @param integer $i
     */
    function setTabIndex($i)
    {
        $this->tabIndex = (int)$i;
    }
    
    /**
     * Set the readonly state of this field
     *
     * @param boolean $b
     * @return Form_Field
     */
    function setReadonly($b)
    {
        $this->readonly = ($b == true);
        return $this;
    }
    
    /**
     * Returns true if the field is readonly
     * 
     * @return boolean
     */
    function isReadonly()
    {
        return $this->readonly;
    }
    
    /**
     * Set the autocomplete state of this field
     *
     * @param boolean $b
     * @return Form_Field
     */
    function setAutocomplete($b)
    {
        $this->autocomplete = ($b == true);
        return $this;
    }
    
    /**
     * Set the tab index of this field
     *
     * @param string $str
     * @return Form_Field
     */
    function setTabGroup($str)
    {
        $this->tabGroup = $str;
        return $this;
    }
    
    /**
     * Get the tab index of this field
     *
     * @return string
     */
    function getTabGroup()
    {
        return $this->tabGroup;
    }
    
    /**
     * Set the notes html
     *
     * @param string $html
     * @return Form_Field
     */
    function setNotes($html)
    {
        $this->notes = $html;
        return $this;
    }
    
    /**
     * Set any javascript code
     *
     * @param string $js
     * @return Form_Field
     */
    function setJavascript($str)
    {
        $this->js = $str;
        return $this;
    }
    
    /**
     * Add an event/attribute to the element
     * EG: onChange, onClick, etc
     *
     * @param string $eventName  onChange, onClick, etc
     * @param string $js
     * @return Form_Field
     */
    function addAttr($attrName, $js)
    {
        $this->attrList[$attrName] = $js;
        return $this;
    }
    
    /**
     * Remove an attribute that has been added via addAttr()
     * @param $attrName
     * @return Form_Field
     */
    function removeAttr($attrName)
    {
        if (isset($this->attrList[$attrName])) {
            unset($this->attrList[$attrName]);
        }
        return $this;
    }
    
    /**
     * Clear the atributes list
     * @return Form_Field
     */
    function clearAttrList()
    {
        $this->attrList = array();
        return $this;
    }
    
    /**
     * Add a style to the form element
     *
     * @param string $style
     * @param string $value
     * @return Form_Field
     */
    function addStyle($style, $value)
    {
        $this->styleList[$style] = $value;
        return $this;
    }
    
    /**
     * Remove a style element
     *
     * @param $style
     * @return Form_Field
     */
    function removeStyle($style)
    {
        if (isset($this->styleList[$style])) {
            unset($this->styleList[$style]);
        }
        return $this;
    }
    
    /**
     * Clear the style list
     * @return Form_Field
     */
    function clearStyleList()
    {
        $this->styleList = array();
        return $this;
    }
    
    /**
     * Set the help tooltip text
     *
     * @param string $str
     * @return Form_Field
     */
    function setHelp($str)
    {
        $this->help = strip_tags($str);
        return $this;
    }
    
    /**
     * Set the field required state
     *
     * @param boolean $b
     * @return Form_Field
     */
    function setRequired($b = true)
    {
        $this->required = ($b === true);
        return $this;
    }
    
    /**
     * Is the field a required field
     *
     * @return boolean
     */
    function isRequired()
    {
        return $this->required;
    }

    
    /**
     * Set the size, limit the number of characters in a standard text input field
     *
     * @param integer $i
     * @return Form_Field
     */
    function setMaxlength($i)
    {
        $this->maxlength = (int)$i;
        return $this;
    }

    
    /**
     * Set the width
     *
     * @param integer $i
     * @return Form_Field
     */
    function setWidth($i)
    {
        $this->width = (int)$i;
        return $this;
    }
    
    
    /**
     * Set the height
     *
     * @param integer $i
     * @return Form_Field
     */
    function setHeight($i)
    {
        $this->height = (int)$i;
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
    }
    
    /**
     * render the structural parts of the form element. This requires the following basic template interface
     *
     * <code>
     *   <?xml version="1.0"?>
     *   <div class="field" var="block">
     *     <p class="error" var="error" choice="error"></p>
     *     <label for="fid-code" var="label">Code:</label>
     *     .... // Some input,select,texarea element
     *     <small var="notes" choice="notes"></small>
     *     <a href="javascript:;" class="admFieldHelp" title="" var="help" choice="help">Help</a>
     *   </div>
     * </code>
     *
     * @param Dom_Template $t
     */
    function showDefault(Dom_Template $t)
    {
        if ($this->hasErrors()) {
            $t->setChoice('error');
            $t->insertHtml('error', $this->getErrrorHtml());
            $t->addClass('block', 'error');
        }
        if ($this->label) {
            $t->insertText('label', $this->label . ':');
        }
        $t->setAttr('label', 'for', $this->getElementId());
        $t->addClass('block', $this->name);
        
        if ($this->notes) {
            $t->setChoice('notes');
            $t->insertHtml('notes', $this->notes);
        }
        if ($this->help) {
            $t->setChoice('help');
            $t->setAttr('help', 'title', $this->help);
        }
        
        if ($this->required) {
            $t->removeClass('block', 'optional');
            $t->addClass('block', 'required');
        } else {
            $t->removeClass('block', 'required');
            $t->addClass('block', 'optional');
        }
        
        if ($this->js) {
            $t->appendJs($this->js);
        }
        $this->showElement($t);
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
        $t->setAttr('element', 'name', $this->name);
        $t->setAttr('element', 'id', $this->getElementId());
        if ($t->getVarElement('element')->nodeName == 'input') {
            if ($this->maxlength > 0) {
                $t->setAttr('element', 'maxlength', $this->maxlength);
            }
            if ($this->value !== null && !is_array($this->getSubFieldValue($this->name))) {
                $t->setAttr('element', 'value', $this->getSubFieldValue($this->name));
            }
        }
    }
    
}