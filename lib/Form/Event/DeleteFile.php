<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Form_Event_DeleteFile
 *
 * @package Form
 */
class Form_Event_DeleteFile extends Form_ButtonEvent
{

    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param string $label
     * @return Form_Event_DeleteFile
     */
    static function create($name)
    {
        $evt = new self($name);
        return $evt;
    }
    
    /**
     * Use this to do any initalisations after the form is set
     *
     * @param Form $form
     */
    function onSetForm($form)
    {
        $controller = $form->getEventController();
        $getMethod = 'get' . ucfirst(str_replace('delete', '', $this->getName()));
        if (!$controller->getObject()->$getMethod()) {
            $this->setEnabled(false);
        }
        $this->addAttr('onclick', 'if (confirm(\'Are you sure you want to delete the attached file?\')) { return true; } else {$(this).unbind(\'click\'); return false; };');
    }
    
    /**
     * Create the full file path from the object path value
     * 
     * @param string $path
     * @return string The full filesystem path to the file
     */
    function getFullPath($path)
    {
        return Tk_Config::get('system.dataPath') . $path;
    }
    
    function execute()
    {
        $object = $this->getObject();
        $name = str_replace('delete', '', $this->getName());
        $getMethod = 'get' . ucfirst($name);
        $setMethod = 'set' . ucfirst($name);
        if (method_exists($object, $getMethod) && $object->$getMethod() != null) {
            @unlink($this->getFullPath($object->$getMethod()));
            $object->$setMethod('');
            $object->update();
        }
        
        //
        $object = $this->getForm()->getEventController()->getObject();
        $name = str_replace('delete', '', $this->getName());
        $url = Tk_Request::requestUri()->delete($this->getName());
        $this->getController()->setRedirectUrl($url);
    }
    
}