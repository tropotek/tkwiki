<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This handles the forms events (buttons)
 * If none are supplied to the form then the default buttons ar added
 *
 * @package Form
 */
class Form_EventController extends Tk_Object implements Tk_Util_CommandInterface
{
    
    
    /**
     * @var Form
     */
    protected $form = null;
    
    /**
     * This will be a copy of the data that will not be changed after the form is submitted
     * @var mixed
     */
    private $originalObject = null;
    
    /**
     * This should be an array or an object with that holds the form data
     * @var mixed
     */
    protected $object = null;
    
    /**
     * @var Tk_Type_Url
     */
    protected $redirectUrl = null;
    
    /**
     * @var Tk_Type_Url
     */
    protected $redirectActive = true;
    
    /**
     * @var array
     */
    protected $eventList = array();
    
    
    
    
    
    /**
     * __construct
     *
     * @param Form $form
     * @param mixed $defaultData An array or object that contains the foms default data
     */
    function __construct(Form $form, $object = array())
    {
        $this->form = $form;
        $this->object = $object;
        $this->originalObject = $object;
        if (is_object($object)) {
            $this->originalObject = clone $object;
        }
    }
    
    /**
     * Create a new form event contorller
     *
     * @param Form $form
     * @param mixed $defaultData An array or object that contains the form default data
     * @return Form_EventController
     */
    static function create(Form $form, $object = array())
    {
        $obj = new self($form, $object);
        return $obj;
    }
    
    /**
     * Add default events save, update, add and cancel
     *
     * @param Tk_Type_Url $eventRedirect
     * @return Form_EventController
     */
    function addDefaultEvents(Tk_Type_Url $eventRedirect)
    {
        if ($this->object && $this->object->getId() > 0) {
            $this->addEvent(Form_Event_Save::create('save'));
            $this->addEvent(Form_Event_Save::create('update')->setRedirectUrl($eventRedirect));
        } else {
            $this->addEvent(Form_Event_Save::create('save'));
            $this->addEvent(Form_Event_Save::create('add')->setRedirectUrl($eventRedirect));
        }
        $this->addEvent(Form_Event_Cancel::create($eventRedirect));
        return $this;
    }
    
    
    
    /**
     * If a redirect url is set then the form is redirected to there
     * 
     * NOTICE: Only call setRedirectUrl() in the events execute functions,
     * as setting it in constructors and before execution will not work.
     * 
     * @param Tk_Type_Url $url
     * @return Form_EventController
     */
    function setRedirectUrl($url)
    {
        $this->redirectUrl = $url;
        return $this;
    }
    
    /**
     * Disable redirect at the end of processing the form
     *
     * @return Form_EventController
     */
    function disableRedirect()
    {
        $this->redirectActive = false;
        return $this;
    }
    
    /**
     * Disable redirect at the end of processing the form
     *
     * @return Form_EventController
     */
    function enableRedirect()
    {
        $this->redirectActive = true;
        return $this;
    }
    
    
    
    /**
     * executed on form submit.
     *
     * @return Form_EventController
     */
    function execute()
    {
        if (Tk_Request::get(Form::HIDDEN_SUBMIT_ID) != $this->form->getFormId()) {
            if ($this->object) {
                $this->loadFromObject();
            }
            if (Tk_Session::exists(Form_Event::MSG_SID.$this->form->getId())) {
                $this->form->setMessageList(Tk_Session::getOnce(Form_Event::MSG_SID.$this->form->getId()));
            }
            $this->processEvent('onLoad');
            return;
        }
        $this->processEvent('onLoad');
        $this->form->loadFromArray(Tk_Request::getInstance()->getAllParameters());
        
        // TODO: test this works
        if (is_array($this->object)) {
            $array = $this->object;
            $this->form->loadArray($array);
        }
        if (is_object($this->object)) {
            $this->form->loadObject($this->object);
        }
        
        
        
        $eventName = $this->findEventName();
        // All form events are executed in the order they were added
        //$this->redirectUrl = null;  // Ensure the redirect url is clear before execution
        
        $this->processEvent('preExecute', $eventName);
        $this->processEvent('execute', $eventName);
        $this->processEvent('postExecute', $eventName);
        
        if ($this->redirectUrl instanceof Tk_Type_Url && !$this->form->hasErrors() && $this->redirectActive) {
            $this->redirectUrl->redirect();
        }
        
        return $this;
    }
    
    
    /**
     * Process an execute function on the array of events.
     * THe event array needs to be processed as a FIFO list
     *
     * Used to process:
     *  o preExecute()
     *  o execute()
     *  o postExecute()
     *
     * @param string $function
     * @param string $eventName
     */
    private function processEvent($function, $eventName = '')
    {
        /* @var $event Form_Event */
        foreach ($this->eventList as $event) {
            if (!$event->isPrimed($eventName)) {
                continue;
            }
            $ev = '';
            if ($eventName) {
                $ev = '(`' . $eventName . '`)';
            }
            Tk::log('Executing Event: ' . $function . $ev . ' - ' . get_class($event) , TK::LOG_INFO);
            $event->$function();
        }
    }
    
    /**
     * This function searched the request and the event list looking for the correct event
     * name to execute.
     *
     * By default it will trigger the first buttonEvent where it finds its name in the request parameter list.
     *
     * @return string
     */
    private function findEventName()
    {
        foreach ($this->eventList as $event) {
            if (!$event instanceof Form_ButtonEvent) {
                continue;
            }
            if (in_array($event->getName(), Tk_Request::getInstance()->getParameterNames())) {
                return $event->getName();
            }
        }
    }
    
    
    
    
    /**
     * Add an event object to the form
     * These objects should contain buttons or similar elements that submit the form
     *
     * @param Form_Event $event
     * @return Form
     */
    function addEvent(Form_Event $event)
    {
        //$this->eventList[] = $event;
        array_unshift($this->eventList, $event);
        $event->setForm($this->form);
        return $event;
    }
    
    /**
     * Clear the event list
     * @return Form
     */
    function clearEventList()
    {
        $this->eventList = array();
        return $this;
    }
    
    /**
     * Get Event List
     *
     * @return array
     */
    function getEventList()
    {
        return $this->eventList;
    }
    
    
    
    /**
     * Load the dfault data into the form
     *
     */
    function loadFromObject()
    {
        if (is_array($this->object)) {
            $this->form->loadFromArray($this->object);
        } else if(is_object($this->object)) {
            $this->form->loadFromObject($this->object);
        }
    }
    
    /**
     * Get the object/array that we will be acting on during this event.
     *
     * @return Tk_Db_Object or an array()
     */
    function getObject()
    {
//        if (!$this->object) {
//            throw new Tk_Exception('No object avaliable to form.');
//        }
        return $this->object;
    }
    
    /**
     * Get the object/array that we will be acting on during this event.
     *
     * @return mixed Tk_Db_Object or and array
     */
    function getOriginalObject()
    {
        return $this->originalObject;
    }
}