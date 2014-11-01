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
class Form_ButtonEvent extends Form_Event
{
    
    const TYPE_SUBMIT = 'submit';
    const TYPE_RESET  = 'reset';
    const TYPE_BUTTON = 'button';
    const TYPE_IMAGE = 'image';
    
    /**
     * @var string
     */
    protected $buttonType = self::TYPE_SUBMIT;
    
    /**
     * @var Tk_Type_Url
     */
    protected $imageUrl = null;
    
    
    /**
     * __construct
     *
     * @param string $name
     */
    function __construct($name)
    {
        parent::__construct($name);
        $this->setRender(true);
    }
    
    /**
     * If an image url is set then the button type is automatically set to TYPE_IMAGE
     *
     * @param Tk_Type_Url $url 
     * @return Form_ButtonEvent
     */
    function setImageUrl(Tk_Type_Url $url)
    {
        $this->setButtonType(self::TYPE_IMAGE);
        $this->imageUrl = $url;
        return $this;
    }
    
    /**
     * set the button type
     * One Of: TYPE_SUBMIT | TYPE_BUTTON | TYPE_RESET | TYPE_IMAGE
     *
     * @param string $type
     * @return Form_ButtonEvent
     */
    function setButtonType($type)
    {
        $this->buttonType = $type;
        return $this;
    }
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $t
     */
    function show($t = null)
    {
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
        $t->setAttr('element', 'name', $this->name);
        //$t->setAttr('element', 'id', $this->getElementId());
        $t->setAttr('element', 'type', $this->buttonType);
        $t->setAttr('element', 'value', $this->label);
        if (!$this->enabled) {
            $t->setAttr('element', 'disabled', 'disabled');
            $t->setAttr('element', 'title', 'disabled');
        }
        if ($this->accessKey) {
            $t->setAttr('element', 'accesskey', $this->accessKey);
        }
        if ($this->tabindex > 0) {
            $t->setAttr('element', 'tabindex', $this->tabindex);
        }
        foreach ($this->attrList as $attr => $js) {
            $t->setAttr('element', $attr, $js);
        }
        if ($this->buttonType == self::TYPE_IMAGE) {
            $t->setAttr('element', 'alt', $this->label);
            $t->setAttr('element', 'border', '0');
            $t->setAttr('element', 'src', $this->imageUrl);
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
<input type="submit" var="element" onclick="$(window).unbind(\'beforeunload\');" />
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }
    
    
    /**
     * (non-PHPdoc)
     * @see Form_Event::addTriger()
     * @return Form_ButtonEvent
     */
    function addTriger($eventName)
    {
        throw new Tk_ExceptionLogic('Cannot add a trigger to a trigger Event.');
        return $this;
    }
    
    /**
     * Set the triger list, overwrites existing trigers
     *
     * @param array $trigerList
     * @return Form_ButtonEvent
     */
    function setTrigerList($trigerList)
    {
        throw new Tk_ExceptionLogic('Cannot add a trigger to a trigger Event.');
        return $this;
    }
    
    
}