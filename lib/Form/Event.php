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
class Form_Event extends Form_Field implements Tk_Util_CommandInterface
{
    
    const MSG_SID = 'Form_Messages';
    
    /**
     * Triggers this event on all/any buttonEvents
     * @var string
     */
    const TRIGER_ON_ALL = 'ALL';
    
    /**
     * @var Form_Field
     */
    protected $bindField = null;
    
    /**
     * @var string
     */
    protected $trigerList = array();
    
    
    /**
     * __construct
     *
     * @param string $name
     */
    function __construct($name = '')
    {
        if (!$name) {
            $name = 'Event-' . time();
        }
        parent::__construct($name);
        $this->setRender(false);
        $this->init();
    }
    
    /**
     * Do any Event initalisations. Call after construct
     *
     * Here you will set the triggers that the event will
     * execute under using Form_Event::setTriggerList() or Form_Event::addTrigger()
     * EG: $this->setTrigerList(array('add', 'save', 'update'));
     *
     */
    function init() { }
    
    /**
     * Executed on form submit event.
     */
    function execute() { }
    
    /**
     * Called when loading the form with the object data
     * You can use this to update new fields to the form.
     *
     */
    function onLoad() { }
    
    /**
     * Pre-execute
     */
    function preExecute() { }
    
    /**
     * Post Execute
     */
    function postExecute() { }
    
    
    
    /**
     * This is the event that will triger execution of the event object.
     * You can add multiple `trigers` these are the event names of button events (EG: 'save', 'add', etc)
     *
     * @param string $eventName
     * @return Form_Event
     */
    function addTriger($eventName)
    {
        $this->trigerList[] = $eventName;
        return $this;
    }
    
    /**
     * Set the triger list, overwrites existing trigers
     *
     * @param array $trigerList
     */
    function setTrigerList($trigerList)
    {
        $this->trigerList = $trigerList;
        return $this;
    }
    
    /**
     * Get the triger list
     *
     * @return array
     */
    function getTriggerList()
    {
        return $this->trigerList;
    }
    
    /**
     * This function checks the $eventName against the trigger list
     * and determins if this event should be trigered, used by the
     * EventController. There should be no need for it to be called in
     * any other code.
     *
     * @param string $eventName
     * @return boolean
     */
    function isPrimed($eventName)
    {
        if (in_array(self::TRIGER_ON_ALL, $this->trigerList)) {
            return true;
        }
        if (($this instanceof Form_ButtonEvent) && $eventName == $this->getName()) {
            return true;
        }
        if (in_array($eventName, $this->trigerList)) {
            return true;
        }
        return false;
    }
    
    /**
     * Get the event controller
     *
     * @return Form_EventController
     */
    function getController()
    {
        return $this->getForm()->getEventController();
    }
    
    /**
     * An alias for the controller::setRedirectUrl()
     *
     * @param Tk_Type_Url $url
     */
    function setRedirect(Tk_Type_Url $url)
    {
        $this->getController()->setRedirectUrl($url);
    }
    
    /**
     * Get the event message
     *
     * @return string
     */
    function setMessage($msg)
    {
        $sid = self::MSG_SID . $this->getForm()->getId();
        $list = array();
        if (Tk_Session::exists($sid)) {
            $list = Tk_Session::get($sid);
        }
        $list[$this->name] = $msg;
        Tk_Session::set($sid, $list);
    }
    
    /**
     * Bind an event to a field when you want that event button/template
     * to be rendered into a field template. Then the field template must contain
     * a var element of the same event name, eg: <div var="deleteImage"></div>
     *
     * <code>
     *  $field = Form_Field_File::create('image');
     *  $event = Form_EventDeleteFile::create($object, 'deleteImage');
     *  $event->bind($field);
     *  $form->addEvent($event);
     * </code>
     *
     * @param Form_Field $bindField
     */
    function bind(Form_Field $bindField)
    {
        $this->bindField = $bindField;
    }
    
    /**
     * Retuirns the field that this event is bound to, null if none.
     *
     * @return Form_Field
     */
    function getField()
    {
        return $this->bindField;
    }
    
    /**
     * Render the widget. No rendering for pure events
     *
     * @param Dom_Template $t
     */
    function show($t = null) { }
    
}