<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A controller interface for objects that are
 * the main controllers of their individual sub-systems.
 *
 * @package Tk
 * @deprecated
 * @todo Move to Tk_Web_InitInterface
 */
interface Tk_Util_InitInterface
{
    /**
     * Do all pre-initalisation operations
     * This method called before the execution method
     */
    function init();
    
    /**
     * Do all post initalisation operations here
     * This method called after the execute method
     */
    function postInit();
}