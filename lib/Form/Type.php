<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *  A type object converts form element values to required types.
 *
 * @package Form
 */
abstract class Form_Type
{
    /**
     * @var Form_Field
     */
    protected $field = null;
    
    
    
    
    
    /**
     * Set the form field.
     * Must be set after construction.
     *
     * @param Form_Field $field
     */
    function setField(Form_Field $field)
    {
        $this->field = $field;
    }
    
    
    /**
     * Get the field name
     *
     * @return string
     */
    function getFieldName()
    {
        return $this->field->getName();
    }
    
    /**
     * Load the field value object from a data sorce array.
     * This is usually, but not limited to, the request array
     *
     * @param array $array
     */
    abstract function loadFromArray($array);
    
    /**
     * Set the raw sub-field values from the field value object
     *
     * @param string $obj
     */
    abstract function setSubFieldValues($obj);
    
    
    /**
     * Convert an object/array to a string
     *
     * @param mixed $obj
     * @return string
     */
    protected function toText($obj)
    {
        if (is_string($obj)) {
            return $obj;
        }
        if (is_object($obj) && method_exists($obj, '__toString')) {
            return $obj->__toString();
        }
        if (is_array($obj)) {
            return implode(',', $obj);
        }
        return $obj;
    }
    
}