<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Form_Event_Save
 *
 * @package Form
 */
class Form_Event_Save extends Form_ButtonEvent
{
    
    /**
     * @var Tk_Type_Url
     */
    protected $redirectUrl = null;
    
    
    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param string $label
     * @return Form_Event_Save
     */
    static function create($name)
    {
        $obj = new self($name);
        return $obj;
    }
    
    /**
     * If a redirect url is set then the form is redirected to there
     *
     * @param Tk_Type_Url $url
     */
    function setRedirectUrl(Tk_Type_Url $url)
    {
        $this->redirectUrl = $url;
        return $this;
    }
    
    
    function execute()
    {
        $object = $this->getObject();
        
        $this->getForm()->addFieldErrors($object->getValidator()->getErrors());
        
        if ($this->getForm()->hasErrors()) {
            return;
        }
        if ($this->getName() == 'save') {
            $this->setMessage('Record saved successfuly.');
        }
        $object->save();
        
        // Set redirect
        $url = Tk_Request::requestUri()->delete('save')->delete('update')->delete('add');
        $arr = explode('_', get_class($object));
        $idVar = lcFirst(array_pop($arr)) . 'Id';
        $url->set($idVar, $object->getId());
        if ($this->redirectUrl) {
            $url = $this->redirectUrl;
        }
        $this->setRedirect($url);
    }
    
    
}