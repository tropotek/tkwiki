<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *
 * @package Com
 */
class Com_Type_MoneyField extends Com_Form_Field
{
    /**
     * Loads the object and the form fields from the request.
     *
     * @param array $array
     * @return Tk_Type_Money
     */
    function loadFromArray($array)
    {
        $name = $this->getName();
        
        $this->domValues[$name] = $array[$name];
        $money = Tk_Type_Money::parseFromString($array[$name]);
        if ($money == null) {
            $money = new Tk_Type_Money(0);
        }
        $this->setValueFromRequest($money);
    }
    
    /**
     * Get this field's string values.
     *
     * @param Tk_Type_Money $value
     */
    function setDomValues($value)
    {
        if ($value == null) {
            $this->domValues->put($this->getName(), '');
        } else {
            $amount = $value->toFloatString();
            $this->domValues[$this->getName()] = $amount;
        }
    }

}