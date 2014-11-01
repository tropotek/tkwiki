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
 */
interface Tk_Util_CommandInterface
{
    
    /**
     * Execute the command
     */
    function execute();

}