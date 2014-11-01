<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 *
 *
 * @package Com
 */
class Com_Type_DateField extends Com_Form_Field
{
    
    /**
     * Loads the object and the form fields from the request.
     *
     * @param array $array
     * @return Tk_Type_Date
     */
    function loadFromArray($array)
    {
        $name = $this->getName();
        $regs = array();
        
        $day = 1;
        $month = 1;
        $year = 1970;
        $hour = 0;
        $minute = 0;
        $second = 0;
        
        if (isset($array[$name])) { // Test for various string formats
            $this->domValues[$name] = $array[$name];
            
            if (preg_match('/^([0-9]{1,2})(\/|-)([0-9]{1,2})(\/|-)([0-9]{2,4})$/', $array[$name], $regs)) { // dd/mm/yyyy
                $day = intval($regs[1]);
                $month = intval($regs[3]);
                $year = intval($regs[5]);
                
                if (isset($array[$name . '_time'])) { // Check if time exists
                    $domTime = $array[$name . '_time'];
                    $this->domValues[$name . '_time'] = $domTime;
                    $arr = explode(':', $domTime);
                    if (count($arr) >= 2) {
                        $hour = intval($arr[0]);
                        $minute = intval($arr[1]);
                        if (isset($arr[2])) {
                            $second = intval($arr[2]);
                        }
                    }
                }
            
            } elseif (preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})( ([0-9]{2}):([0-9]{2})(:([0-9]{2}))?)?$/', $array[$name], $regs)) {  // yyyy-mm-dd hh:mm:ss
                $day = intval($regs[3]);
                $month = intval($regs[2]);
                $year = intval($regs[1]);
                if (count($regs) > 4) {
                    $hour = intval($regs[5]);
                    $minute = intval($regs[6]);
                    if (isset($regs[8])) {
                        $second = intval($regs[8]);
                    }
                }
            
            } else {
                //vd('Invalid Date Format. Should we throw an exception here?', $regs, $name);
                return;
            }
        } else if (isset($array[$name . '_day']) || isset($array[$name . '_hour'])) { // Test for multiple date select fields
            // Get Date
            if (isset($array[$name . '_day'])) {
                $day = intval($array[$name . '_day']);
                $this->domValues[$name . '_day'] = $array[$name . '_day'];
                $month = intval($array[$name . '_month']);
                $this->domValues[$name . '_month'] = $array[$name . '_month'];
                $year = intval($array[$name . '_year']);
                $this->domValues[$name . '_year'] = $array[$name . '_year'];
            }
            // Get Time
            if (isset($array[$name . '_time'])) { // Check if time field exists
                $domTime = $array[$name . '_time'];
                $this->domValues[$name . '_time'] = $domTime;
                $arr = explode(':', $domTime);
                if (count($arr) >= 2) {
                    $hour = intval($arr[0]);
                    $minute = intval($arr[1]);
                    if (isset($arr[2])) {
                        $second = intval($arr[2]);
                    }
                }
            } else if (isset($array[$name . '_hour'])) { // Get time from multiple select boxes
                $hour = $array[$name . '_hour'];
                $this->domValues[$name . '_hour'] = $array[$name . '_hour'];
                
                $minute = $array[$name . '_minute'];
                $this->domValues[$name . '_minute'] = $array[$name . '_minute'];
                if (isset($array[$name . '_second'])) {
                    $second = $array[$name . '_second'];
                    $this->domValues[$name . '_second'] = $array[$name . '_second'];
                }
            
            }
        }
        
        //vd($hour, $minute, $second, $month, $day, $year);
        // Make and return the date object
        $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
        $date = Tk_Type_Date::createDate($timestamp);
        
        $this->setValueFromRequest($date);
    }
    
    /**
     * Get this field's Dom string values.
     *
     * @param Tk_Type_Date $value
     */
    protected function setDomValues($value)
    {
        $name = $this->getName();
        if ($value == null) {
            $this->domValues[$name] = '';
            $this->domValues[$name . '_day'] = '';
            $this->domValues[$name . '_month'] = '';
            $this->domValues[$name . '_year'] = '';
            $this->domValues[$name . '_time'] = '';
            $this->domValues[$name . '_hour'] = '';
            $this->domValues[$name . '_minute'] = '';
            $this->domValues[$name . '_second'] = '';
        } else {
            $this->domValues[$name] = $value->toString('d/m/Y');
            $this->domValues[$name . '_day'] = $value->toString('d');
            $this->domValues[$name . '_month'] = $value->toString('m');
            $this->domValues[$name . '_year'] = $value->toString('Y');
            $this->domValues[$name . '_time'] = $value->getTime();
            $this->domValues[$name . '_hour'] = $value->toString('H');
            $this->domValues[$name . '_minute'] = $value->toString('i');
            $this->domValues[$name . '_second'] = $value->toString('s');
        }
    }

}
