<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The Date object to handle Date functions.
 *
 * @package Tk
 */
class Tk_Type_Date extends Tk_Object
{
    
    /**
     * Long Date
     * Tuesday, 23 Apr 2009
     */
    const F_LONG_DATE = 'l, j M Y';
    /**
     * Long Date With Time
     * Tuesday, 01 Jan 2009 12:59 PM
     */
    const F_LONG_DATETIME = 'l, j M Y h:i A';
    /**
     * Short Date With Time
     * 23/09/2009 24:59:59
     */
    const F_SHORT_DATETIME = 'd/m/Y H:i:s';
    /**
     * Medium Date
     * 23 Apr 2009
     */
    const F_MED_DATE = 'j M Y';
    
    /**
     * @var integer
     */
    private $timestamp = 0;
    
    /**
     * Month end days.
     * @var array
     */
    private static $monthEnd = array('1' => '31', '2' => '28', '3' => '31', '4' => '30', '5' => '31', '6' => '30', '7' => '31', '8' => '31', '9' => '30', '10' => '31', '11' => '30', '12' => '31');
    
    /**
     *Create a date object ustin a timestamp as the value
     *
     * @param integer $timestamp A unix timestamp.
     */
    function __construct($timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = time();
        }
        $this->timestamp = $timestamp;
    }
    
    /**
     * Create a date from a static context.
     *
     * @param integer $timeStamp
     * @return Tk_Type_Date
     */
    static function create($timestamp = null)
    {
        return new Tk_Type_Date($timestamp);
    }
    
    /**
     * Create a date from a static context.
     *
     * @param integer $timeStamp
     * @return Tk_Type_Date
     * @deprecated Use ::create()
     */
    static function createDate($timestamp = null)
    {
        return self::create($timestamp);
    }
    
    /**
     * Create an Tk_Type_Date object from ISO date time.
     * ISO Format is:
     *        yyyy-mm-dd hh:mm:ss, where
     *  o yyyy is a four digit numeral that represents the year.
     *  o the remaining '-'s are separators between parts of the date portion;
     *  o the first mm is a two-digit numeral that represents the month;
     *  o dd is a two-digit numeral that represents the day;
     *  o hh is a two-digit numeral that represents the hour.
     *  o ':' is a separator between parts of the time-of-day portion;
     *  o the second mm is a two-digit numeral that represents the minute;
     *  o ss is a two-integer-digit numeral that represents the whole seconds;
     *
     * @param string $iso
     * @return Tk_Type_Date Returns null if $iso_date is not a valid ISO date.
     */
    static function parseIso($isoDate)
    {
        $regs = null;
        if ($isoDate == null || $isoDate == '0000-00-00 00:00:00') {
            return null;
        }
        if (!preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})[T ]?(([0-9]{1,2}):([0-9]{1,2})(:([0-9]{1,2}))?)?$/', $isoDate, $regs)) {
            return null;
        }
        if (!checkdate($regs[2], $regs[3], $regs[1])) {
            return null;
        }
        $timestamp = mktime(0, 0, 0, $regs[2], $regs[3], $regs[1]);
        
        // check time
        if (isset($regs[5]) && $regs[5] != '') {
            if ($timestamp === false) {
                $regs[1] = 1970;
                $regs[2] = 1;
                $regs[3] = 1;
            }
            if (isset($regs[8]) && $regs[8] != '') {
                $timestamp = mktime($regs[5], $regs[6], $regs[8], $regs[2], $regs[3], $regs[1]);
            } else {
                $timestamp = mktime($regs[5], $regs[6], 0, $regs[2], $regs[3], $regs[1]);
            }
        }
        // If all else fails return false
        if ($timestamp === null) {
            return null;
        }
        
        return new Tk_Type_Date($timestamp);
    }
    
    /**
     * Create a date object from the date in the format of dd/mm/yyyy
     * @param string $str
     * @return Tk_Type_Date
     */
    static function parseShortDate($str)
    {
        if (preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/([0-9]{2})?[0-9]{4}$/', $str)) {
            $arr = explode('/', $str);
            $timestamp = mktime(0, 0, 0, $arr[1], $arr[0], $arr[2]);
            return new Tk_Type_Date($timestamp);
        } else {
            return null;
        }
    }
    
    /**
     * Get the months ending date 1 = 31-Jan, 12 = 31-Dec
     *
     * @param integer $m
     * @param integer $y
     * @return integer
     */
    static function getMonthLastDate($m, $y = '')
    {
        if ($m == 2) { // feb test for leap year
            if (self::isLeapYear($y)) {
                return self::$monthEnd[$m] + 1;
            }
        }
        return self::$monthEnd[$m];
    }
    
    /**
     * Is the supplied year a leap year
     *
     * @param integer $y
     * @param boolean
     */
    static function isLeapYear($y)
    {
        if ($y % 4 != 0) {
            return false; // use 28 for days in February
        } else if ($y % 400 == 0) {
            return true; // use 29 for days in February
        } else if ($y % 100 == 0) {
            return false; // use 28 for days in February
        } else {
            return true; // use 29 for days in February
        }
    }
    
    /**
     * Return this dates timestamp
     *
     * @return integer
     */
    function getTimestamp()
    {
        return $this->timestamp;
    }
    
    /**
     * Set the time of a date object to 23:59:59
     *
     * @return Tk_Type_date
     */
    function ceil()
    {
        $ts = mktime(23, 59, 59, $this->getMonth(), $this->getDate(), $this->getYear());
        return new Tk_Type_Date($ts);
    }
    
    /**
     * Set the time of a date object to 00:00:00
     *
     * @return Tk_Type_date
     */
    function floor()
    {
        $ts = mktime(0, 0, 0, $this->getMonth(), $this->getDate(), $this->getYear());
        return new Tk_Type_Date($ts);
    }
    
    /**
     * Get the first day of this dates month
     *
     * @return Tk_Type_Date
     */
    function getMonthFirstDay()
    {
        $ts = mktime(0, 0, 0, $this->getMonth(), 1, $this->getYear());
        return self::createDate($ts);
    }
    
    /**
     * Get the last day of this dates month
     *
     *
     * @return Tk_Type_Date
     */
    function getMonthLastDay()
    {
        $lastDay = self::getMonthLastDate($this->getMonth(), $this->getYear());
        $ts = mktime(23, 59, 59, $this->getMonth(), $lastDay, $this->getYear());
        return self::createDate($ts);
    }
    
    /**
     * Adds days to date and returns a new instance.
     * NOTE: Days are calculated as ($days * 86400)
     *
     * To subtract days, use a negative number of days.
     * @param integer $days
     * @return Tk_Type_Date
     */
    function addDays($days)
    {
        return new Tk_Type_Date($this->getTimestamp() + ($days * 86400));
    }
    
    /**
     * Add seconds to a date.
     *
     * @param integer $sec
     * @return Tk_Type_Date
     */
    function addSeconds($sec)
    {
        return new Tk_Type_Date($this->getTimestamp() + $sec);
    }
    
    /**
     * Add actual months to a date
     *
     * @param integer $months
     * @return Tk_Type_Date
     */
    function addMonths($months)
    {
        $ts = mktime($this->getHour(), $this->getMinute(), $this->getSecond(), $this->getMonth() + $months, 1, $this->getYear());
        $tmpDate = self::createDate($ts);
        if ($this->getDate() == self::getMonthLastDate($this->getMonth(), $this->getYear()) || $this->getDate() > $tmpDate->getMonthLastDay()->getDate()) {
            $ts = mktime($this->getHour(), $this->getMinute(), $this->getSecond(), $this->getMonth() + $months, $tmpDate->getMonthLastDay()->getDate(), $this->getYear());
        } else {
            $ts = mktime($this->getHour(), $this->getMinute(), $this->getSecond(), $this->getMonth() + $months, $this->getDate(), $this->getYear());
        }
        $date = self::createDate($ts);
        return $date;
    }
    
    /**
     * Add actual years to a date
     *
     * @param integer $years
     * @return Tk_Type_Date
     */
    function addYears($years)
    {
        $ts = mktime($this->getHour(), $this->getMinute(), $this->getSecond(), $this->getMonth(), $this->getDate(), $this->getYear() + $years);
        return self::createDate($ts);
    }
    
    /**
     * Returns the difference between this date and other in days.
     *
     * @param Tk_Type_Date $other
     * @return integer
     */
    function dayDifference(Tk_Type_Date $other)
    {
        return ceil(($this->getTimestamp() - $other->getTimestamp()) / 86400);
    }
    
    /**
     * Return the diffrence between this date and other in hours.
     *
     * @param Tk_Type_Date $other
     * @return integer
     */
    function hourDiffrence(Tk_Type_Date $other)
    {
        return ceil(($this->getTimestamp() - $other->getTimestamp()) / 3600);
    }
    
    /**
     * Compares the value to another instance of date.
     *
     * @param Tk_Type_Date $other
     * @return integer Returns -1 if less than , 0 if equal to, 1 if greater than.
     */
    function compareTo(Tk_Type_Date $other)
    {
        $retVal = 1;
        if ($this->getTimestamp() < $other->getTimestamp()) {
            $retVal = -1;
        } elseif ($this->getTimestamp() == $other->getTimestamp()) {
            $retVal = 0;
        }
        
        return $retVal;
    }
    
    /**
     * Checks if the date value is greater than the value of another instance of date.
     *
     * @param Tk_Type_Date
     * @return boolean
     */
    function greaterThan(Tk_Type_Date $other)
    {
        return ($this->compareTo($other) > 0);
    }
    /**
     * Checks if the date value is greater than or equal the value of another instance of date.
     *
     * @param Tk_Type_Date
     * @return boolean
     */
    function greaterThanEqual(Tk_Type_Date $other)
    {
        return ($this->compareTo($other) >= 0);
    }
    
    /**
     * Checks if the date value is less than the value of another instance of date.
     *
     * @param Tk_Type_Date
     * @return boolean
     */
    function lessThan(Tk_Type_Date $other)
    {
        return ($this->compareTo($other) < 0);
    }
    
    /**
     * Checks if the date value is less than or equal the value of another instance of date.
     *
     * @param Tk_Type_Date
     * @return boolean
     */
    function lessThanEqual(Tk_Type_Date $other)
    {
        return ($this->compareTo($other) <= 0);
    }
    
    /**
     * Checks if the date is equal to the value of another instance of date.
     *
     * @param Tk_Type_Date
     * @return boolean
     */
    function equals(Tk_Type_Date $other)
    {
        return ($this->compareTo($other) == 0);
    }
    
    /**
     * Get the integer value for the hour
     *
     * @return integer
     */
    function getHour()
    {
        return intval($this->toString('H'), 10);
    }
    
    /**
     * Get the integer value of teh minute
     *
     * @return integer
     */
    function getMinute()
    {
        return intval($this->toString('i'), 10);
    }
    
    /**
     * Get the seconds integer value
     *
     * @return integer
     */
    function getSecond()
    {
        return intval($this->toString('s'), 10);
    }
    
    /**
     * Get the integer value of the day date.
     *
     * @return integer
     */
    function getDate()
    {
        return intval($this->toString('j'), 10);
    }
    
    /**
     * Get the integer value of the month
     *
     * @return integer
     */
    function getMonth()
    {
        return intval($this->toString('n'), 10);
    }
    
    /**
     * Get the 4 digit integer value of the year
     *
     * @return integer
     */
    function getYear()
    {
        return intval($this->toString('Y'), 10);
    }
    
    /**
     * Get the financial year of this date
     * list($start, $end) = Tk_Type_Date::createDate()->getFinancialYear();
     *
     * @return array
     */
    function getFinancialYear()
    {
        $startYear = $this->getYear();
        $endYear = $this->getYear() + 1;
        if ($this->getMonth() < 7) {
            $startYear = $this->getYear() - 1;
            $endYear = $this->getYear();
        }
        $start = self::createDate(mktime(0, 0, 0, 7, 1, $startYear));
        $end = self::createDate(mktime(23, 59, 59, 6, 30, $endYear));
        return array($start, $end);
    }
    
    /**
     * Gets an ISO (yyyy-mm-dd hh:mm:ss) formated string representation of the date.
     * Usful for DB entries.
     *
     * @param boolean $time If false then only the date protion is returned
     * @return string
     */
    function getIsoDate($time = true)
    {
        if ($time) {
            return $this->toString('Y-m-d H:i:s');
        }
        return $this->toString('Y-m-d');
    }
    
    /**
     * Get the UTC version of the date, I think this works needs more testing.....
     *
     * @return Tk_Type_Date
     */
    function getUTCDate()
    {
        return Tk_Type_date::create($this->timestamp - (int)$this->toString('Z'));
    }
    
    /**
     * Get a short date in the format of 'dd/mm/yyyy'
     *
     * @return string
     */
    function getShortDate()
    {
        return $this->toString('d/m/Y');
    }
    
    /**
     * Get a time in the format of 'HH:mm:ss'
     *
     * @param boolean $showTime
     * @return string
     */
    function getTime()
    {
        return $this->toString('H:i:s');
    }
    
    /**
     * Returns an ISO string of the date value
     * Optionally a format string ccan be passed that will be parsed by 
     * the PHP date() function.
     *
     * @param string $format Optional date format string
     * @return string
     * @see http://au.php.net/manual/en/function.date.php date() function in the php manual.
     */
    function toString($format = '')
    {
        if (!$format) {
            return $this->getIsoDate();
        } else {
            return date($format, $this->getTimestamp());
        }
    }
    
    /**
     * only show time for todays dates show whole date for other dates...
     *
     * @return string
     */
    function toDynString()
    {
        $now = Tk_Type_Date::create();
        if ($now->floor()->equals($this->floor())) {
            return $this->toString('H:i');
        } else {
            return $this->toString('d/m/Y H:i');
        }
    }
}
