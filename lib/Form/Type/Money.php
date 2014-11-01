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
class Form_Type_Money extends Form_Type
{

    /**
     * Create an instance of this object
     *
     * @return Form_Type_Money
     */
    static function create()
    {
        return new self();
    }
    
    /**
     * Load the field value object from a data sorce array.
     * This is usually, but not limited to, the request array
     *
     * @param array $array
     */
    function loadFromArray($array)
    {
        $name = $this->getFieldName();
        if (!array_key_exists($name, $array)) {
            return;
        }
        $this->field->setSubFieldValue($name, $array[$name]);
        if ($array[$name] === '' || $array[$name] === null ) {
        	return;
        }
        $money = Tk_Type_Money::parseFromString($array[$name]);
        $this->field->setRawValue($money);
    }
    
    /**
     * Set the raw sub-field values from the field value object
     *
     * @param Tk_Type_Money $obj
     */
    function setSubFieldValues($obj)
    {
        $name = $this->getFieldName();
        $this->field->setSubFieldValue($name, '');
        if ($obj) {
            $amount = $obj->toFloatString();
            $this->field->setSubFieldValue($name, $amount);
        }
    }
    
}