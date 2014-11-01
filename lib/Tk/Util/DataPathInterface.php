<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An interface for an object to return its data path.
 * This is usually the path in the data directory that
 * the object using to store its uploaded media to.
 * This can be used for large object to ensure the object's data
 * is allways stored in the same directory.
 *
 * NOTE: All paths returned must be relative from the site FileRoot
 * the site file root path can be added during access. This allows for portability
 * between the Tk_Type_Path and Tk_Type_Url objects.
 *
 * @package Tk
 * @deprecated
 * @todo remove
 */
interface Tk_Util_DataPathInterface
{
    
    /**
     * Get the object data path relative to the site file root
     * For a standard data file the path might be: `/data/files/thumbs`
     *
     * @return Tk_Type_Path
     */
    function getDataPath();

}