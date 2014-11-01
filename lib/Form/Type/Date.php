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
class Form_Type_Date extends Form_Type
{

    /**
     * Create an instance of this object
     *
     * @return Form_Type_Date
     */
    static function create()
    {
        $obj = new self();
        return $obj;
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
        $now = Tk_Type_Date::create();
        $day = 1;
        $month = 1;
        $year = 1970;
        $hour = $now->getHour();
        $minute = $now->getMinute();
        $second = $now->getSecond();
        
        $regs = array();
        if (preg_match('/^([0-9]{1,2})(\/|-)([0-9]{1,2})(\/|-)([0-9]{2,4})$/', $array[$name], $regs)) { // dd/mm/yyyy  OR   dd-mm-yyyy
            $day = intval($regs[1]);
            $month = intval($regs[3]);
            $year = intval($regs[5]);
            $this->field->setRawValue(Tk_Type_Date::create(mktime($hour, $minute, $second, $month, $day, $year)));
            return;
        }
        $this->field->setRawValue(null);
    }
    
    /**
     * Set the raw sub-field values from the field value object
     *
     * @param Tk_Type_Date $obj
     */
    function setSubFieldValues($obj)
    {
        $name = $this->getFieldName();
        $this->field->setSubFieldValue($name, '');
        if ($obj) {
            $this->field->setSubFieldValue($name, $obj->toString('d/m/Y'));
        }
    }
    
}