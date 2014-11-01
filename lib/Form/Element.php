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
abstract class Form_Element extends Dom_Renderer
{
    
    /**
     * @var string
     */
    protected $name = '';
    
    /**
     * An object representing this field's value
     * @var mixed
     */
    protected $value = null;
    
    /**
     * This will convert the value to the required data type
     * @var Form_Type
     */
    protected $type = null;
    
    /**
     * An array of all sub-field values
     * Usually only one sub-field is used but needed
     * in-case multiple fields are used, eg: date/month/year or hh:mm
     * where each sub-value is required to create a single object
     *
     * @var array
     */
    protected $subFieldValues = array();
    
    /**
     * @var array
     */
    protected $errors = array();
    
    /**
     * If this is false the field value will not be loaded during the
     * Form method calls: loadObject() and getValuesArray()
     *
     * @var boolean
     */
    protected $loadable = true;
    
    

    
    /**
     * __construct
     *
     * @param string $name
     * @param Form_Type $type
     */
    function __construct($name, $type = null)
    {
        $this->name = $name;
        if (!$type instanceof Form_Type) {
        	$type = Form_Type_String::create();
        }
        $this->setType($type);
    }
    

    
    
    /**
     * Set this object's unique ID
     *
     * @param mixed $i
     * @return Form_Element
     */
    function setId($i)
    {
        $this->id = $i;
        return $this;
    }
    
    /**
     * Get the event name
     *
     * @return string
     */
    function getName()
    {
        if (preg_match('/]$/', $this->name)) {
            return substr($this->name, 0, strpos($this->name, '['));
        }
        return $this->name;
    }
    
    /**
     * Get the `id` attribute value
     *
     * @return string
     */
    function getElementId()
    {
    	$name = preg_replace('/[^a-z0-9]/i', '', $this->name);
        return 'fid-' . $name;
    }
    
    /**
     * set the value type object
     *
     * @param Form_Type $type
     * @return Form_Field
     */
    function setType(Form_Type $type)
    {
        $this->type = $type;
        $this->type->setField($this);
        return $this;
    }
    
    /**
     * Return the type converter object
     *
     * @return Form_Type
     */
    function getType()
    {
        return $this->type;
    }
    
    /**
     * If this is false the field value will not be loaded during the
     * Form method calls: loadObject() and getValuesArray()
     *
     * @param boolean $b
     */
    function setLoadable($b)
    {
        $this->loadable = ($b === true);
    }
    
    /**
     * Get the field loadable status
     *  @return boolean
     */
    function isLoadable()
    {
        return $this->loadable;
    }
    
    /**
     * Add an error message html to the element
     *
     * @param string $html
     * @return Form_Field
     */
    function addError($html)
    {
        $html = strip_tags($html, 'b,strong,i,em,br,font,cite,abbr,del,ins,code,pre,acronym,a,img');
        $this->errors[] = $html;
        return $this;
    }
    
    /**
     * Clear the error list
     *
     * @return Form_Field
     */
    function clearErrorList()
    {
        $this->errors = array();
        return $this;
    }
    
    /**
     * Get the error as a formatted html string
     *
     * @return string
     */
    function getErrrorHtml()
    {
        $html = '';
        foreach ($this->errors as $i => $msg) {
            $html .= $msg;
            if ($i < count($this->errors)-1) {
                $html .= "<br/>\n";
            }
        }
        return $html;
    }
    
    /**
     * Does this element contain errors
     *
     * @return boolean
     */
    function hasErrors()
    {
        return count($this->errors) ? true : false;
    }
    
    /**
     * Set the value of the element from a mixed type
     *
     * @param mixed $value
     * @return Form_Field
     */
    function setValue($value)
    {
        $this->value = $value;
        $this->type->setSubFieldValues($value);
        return $this;
    }
    
    /**
     * replace the value parameter ignoring the subFieldValues
     *
     * @param mixed $value
     */
    function setRawValue($value)
    {
        $this->value = $value;
    }
    
    /**
     * Get the field's value object
     *
     * @return mixed
     */
    function getValue()
    {
        return $this->value;
    }
    
    /**
     * Set the raw sub-field values from the form field query
     *
     * @param string $key
     * @param string $value
     */
    function setSubFieldValue($key, $value = null)
    {
        if ($value === null) {
            unset($this->subFieldValues[$key]);
            return;
        }
        $this->subFieldValues[$key] = $value;
    }
    
    /**
     * Get a sub field value
     *
     * @param string $key
     * @return string
     */
    function getSubFieldValue($key)
    {
        if (isset($this->subFieldValues[$key])) {
            return $this->subFieldValues[$key];
        }
        return '';
    }
    
    /**
     * Get the sub field value list
     *
     * @return array
     */
    function getSubFieldValueList()
    {
        return $this->subFieldValues;
    }
    
    
    
    function show() {}
    
}