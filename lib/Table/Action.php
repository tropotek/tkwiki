<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The dynamic table Cell
 *
 *
 * @package Table
 */
abstract class Table_Action extends Table_Element
{
    
    /**
     * @var string
     */
    protected $event = '';
    
    /**
     * @var string
     */
    protected $label = '';
    
    /**
     * @var string
     */
    protected $notes = '';
    
    /**
     * @var array
     */
    protected $class = array();
    
    /**
     * @var Tk_Type_Url
     */
    protected $url = null;
    
    
    /**
     * Create
     *
     * @param string $eventName
     */
    function __construct($eventName, $url = null)
    {
        $this->event = $eventName;
        $this->label = ucfirst(preg_replace('/[A-Z]/', ' $0', $this->event));
        if (!$url) {
            $url = Tk_Request::requestUri()->set($this->getEventKey($this->event), $this->event);
        }
        $this->url = $url;
    }
    
    
    /**
     * Get the action HTML to insert into the Table.
     * If you require to use form data be sure to submit the form using javascript not just a url anchor.
     * Use submitForm() found in Js/Util.js to submit a form with an event
     *
     * @param array $list
     * @return Dom_template You can also return HTML string
     */
    function getHtml($list)
    {
        return sprintf('<a class="%s" href="%s" title="%s">%s</a>', $this->getClassString(), $this->url->toString(), $this->notes, $this->text);
    }
        
    /**
     * Return the conncatenated class array
     *
     * @return string
     */
    protected function getClassString()
    {
        $class = 'i16-default';
        if (count($this->class)) {
            $class = trim(implode(' ', $this->class));
        }
        return $class;
    }
    
    /**
     * Get the action label text
     *
     * @return string
     */
    function getLabel()
    {
        return $this->label;
    }
    
    /**
     * Set the label text of this action
     *
     * @param string $str
     * @return Table_Action
     */
    function setLabel($str)
    {
        $this->label = $str;
        return $this;
    }
    
    /**
     * Get the notes text
     *
     * @return string
     */
    function getNotes()
    {
        return $this->notes;
    }
    
    /**
     * Set the notes of this action
     * This text will be uset as a tooltip or explanation of the action where aplicable
     *
     * @param string $str
     * @return Table_Action
     */
    function setNotes($str)
    {
        $this->notes = $str;
        return $this;
    }
    
    /**
     * Set the url
     *
     * @param Tk_Type_Url $url
     * @return Table_Action
     */
    function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
    
    /**
     * Get the URL
     *
     * @return Tk_Type_Url
     */
    function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Get the cell event
     *
     * @return string
     */
    function getEvent()
    {
        return $this->event;
    }
    
    /**
     * Add a cell css class
     *
     * @param string $class
     * @return Table_Cell
     */
    function addClass($class)
    {
        $this->class[$class] = $class;
        return $this;
    }
    
    /**
     * remove a css class
     *
     * @param string $class
     * @return Table_Cell
     */
    function removeClass($class)
    {
        unset($this->class[$class]);
        return $this;
    }
    
    /**
     * Get the css class list
     *
     * @return array
     */
    function getClassList()
    {
        return $this->class;
    }
    
    
}
