<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Render a 3 select box date element.
 *
 * The select elements must be named like the following:
 * <code>
 *     <div class="row">
 *       <div class="label">From: </div>
 *       <div class="input">
 *         <select name="startDate_day">
 *           <option>01</option>
 *           <option>02</option>
 *         </select>
 *         <select name="startDate_month">
 *           <option>01</option>
 *           <option>02</option>
 *           <option>03</option>
 *         </select>
 *         <select name="startDate_year">
 *           <option>2006</option>
 *           <option>2007</option>
 *           <option>2008</option>
 *         </select>
 *       </div>
 *     </div>
 * </code>
 *
 * The group name is the first part of the name in this example it is 'startDate'.
 *
 *
 * @package Com
 */
class Com_Ui_DateSelect extends Dom_Renderer
{
    /**
     * @var Dom_Form_Object
     */
    private $domForm = null;
    
    /**
     * @var Tk_Type_Date
     */
    private $selectedDate = null;
    
    /**
     * @var string
     */
    private $groupName = '';
    
    /**
     * @var inster
     */
    private $yearsActive = 20;
    
    /**
     * @var boolean
     */
    private $ignorePast = false;
    
    /**
     * @var boolean
     */
    private $monthNames = false;
    
    /**
     * Set the template.
     *
     * @param Dom_Form $domForm
     */
    function __construct(Dom_Form $domForm, $groupName)
    {
        $this->domForm = $domForm;
        $this->groupName = $groupName;
        
        $this->selectedDate = Tk_Type_Date::createDate();
    }
    
    /**
     * Render the widget.
     *
     * @param Dom_Template $template
     */
    function show()
    {
        
        if ($this->domForm->getFormElement($this->groupName . '_day') != null) {
            $this->showDay($this->domForm->getFormElement($this->groupName . '_day'));
        }
        if ($this->domForm->getFormElement($this->groupName . '_month') != null) {
            $this->showMonth($this->domForm->getFormElement($this->groupName . '_month'));
        }
        if ($this->domForm->getFormElement($this->groupName . '_year') != null) {
            $this->showYear($this->domForm->getFormElement($this->groupName . '_year'));
        }
        
        if ($this->domForm->getFormElement($this->groupName . '_hour') != null) {
            $this->showHour($this->domForm->getFormElement($this->groupName . '_hour'));
        }
        if ($this->domForm->getFormElement($this->groupName . '_minute') != null) {
            $this->showMinute($this->domForm->getFormElement($this->groupName . '_minute'));
        }
        if ($this->domForm->getFormElement($this->groupName . '_second') != null) {
            $this->showSecond($this->domForm->getFormElement($this->groupName . '_second'));
        }
    
    }
    
    /**
     * Show the number of days and set to selected date
     *
     * @param Dom_FormSelect $daySelect
     */
    function showDay(Dom_FormSelect $daySelect)
    {
        $daySelect->removeOptions();
        $maxDays = Tk_Type_Date::getMonthEnd($this->selectedDate->getMonth(), $this->selectedDate->getYear());
        for($i = 1; $i <= $maxDays; $i++) {
            $strI = $i;
            if ($strI < 10) {
                $strI = '0' . $strI;
            }
            $daySelect->appendOption($strI);
        }
        $daySelect->setValue($this->selectedDate->toString('d'));
    }
    
    /**
     * set 12 months from 01 - 12 and select this month
     *
     * @param Dom_FormSelect $monthSelect
     */
    function showMonth(Dom_FormSelect $monthSelect)
    {
        $monthSelect->removeOptions();
        $numMonths = 12;
        for($i = 1; $i <= $numMonths; $i++) {
            $strI = $i;
            if ($strI < 10) {
                $strI = '0' . $strI;
            }
            $strM = $strI;
            if ($this->monthNames) {
                $strM = Tk_Type_Date::getMonthAbbrev($i);
            }
            $monthSelect->appendOption($strM, $strI);
        }
        $monthSelect->setValue($this->selectedDate->toString('m'));
    }
    
    /**
     * Set to 10 years back and 10 years forward
     * And select this year
     *
     * @param Dom_FormSelect $yearSelect
     */
    function showYear(Dom_FormSelect $yearSelect)
    {
        $thisYear = $this->selectedDate->getYear();
        $yearSelect->removeOptions();
        if ($this->ignorePast) {
            $start = $thisYear;
        } else {
            $start = $thisYear - $this->yearsActive;
        }
        for($i = $start; $i <= $thisYear + $this->yearsActive; $i++) {
            $yearSelect->appendOption($i);
        }
        $yearSelect->setValue($this->selectedDate->getYear());
    }
    
    /**
     * Show the number of days and set to selected date
     *
     * @param Dom_FormSelect $daySelect
     */
    function showHour(Dom_FormSelect $select)
    {
        $select->removeOptions();
        $max = 23;
        for($i = 0; $i <= $max; $i++) {
            $strI = $i;
            if ($strI < 10) {
                $strI = '0' . $strI;
            }
            $select->appendOption($strI);
        }
        $select->setValue($this->selectedDate->toString('H'));
    }
    
    /**
     * Show the number of days and set to selected date
     *
     * @param Dom_FormSelect $daySelect
     */
    function showMinute(Dom_FormSelect $select)
    {
        $select->removeOptions();
        $max = 59;
        for($i = 0; $i <= $max; $i++) {
            $strI = $i;
            if ($strI < 10) {
                $strI = '0' . $strI;
            }
            $select->appendOption($strI);
        }
        $select->setValue($this->selectedDate->toString('i'));
    }
    
    /**
     * Show the number of days and set to selected date
     *
     * @param Dom_FormSelect $daySelect
     */
    function showSecond(Dom_FormSelect $select)
    {
        $select->removeOptions();
        $max = 59;
        for($i = 0; $i <= $max; $i++) {
            $strI = $i;
            if ($strI < 10) {
                $strI = '0' . $strI;
            }
            $select->appendOption($strI);
        }
        $select->setValue($this->selectedDate->toString('s'));
    }
    
    /**
     * Set the selected date. Defaults to the current date and time.
     *
     * @param Tk_Type_Date $date
     */
    function setSelectedDate(Tk_Type_Date $date)
    {
        $this->selectedDate = $date;
    }
    
    /**
     * If set to true no past dates are rendered.
     *
     * @param boolean $b
     */
    function setIgnorePast($b)
    {
        $this->ignorePast = $b;
    }
    
    /**
     * If set to true month names are displayed in the month box.
     *
     * @param boolean $b
     */
    function showMonthNames($b)
    {
        $this->monthNames = $b;
    }
    
    /**
     * Set the number of years to be active (default 20)
     *
     * @param integer $i
     */
    function setYearsActive($i)
    {
        $this->yearsActive = $i;
    }
}