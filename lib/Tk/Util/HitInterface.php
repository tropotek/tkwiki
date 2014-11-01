<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * An interface to record hits to an object. Good for blogs, cms etc.
 *
 * @package Tk
 * @deprecated
 * @todo Move to another lib
 */
interface Tk_Util_HitInterface
{
    /**
     * Get the unique object ID
     *
     * @return integer $i
     */
    function getId();
    
    /**
     * Update the object persistent storage data.
     *
     */
    function update();
}