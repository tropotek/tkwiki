<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This is the loader interface and should be used on the object or its loader
 * to generate a datamap, other functions can be created to construct different maps
 * however there must at least be one data map function available
 *
 * getDataMap() can and will be called by default for all objects using the data loader,
 * if no dataMap is supplied.
 *
 *
 * @package Tk
 */
interface Tk_Loader_Interface
{
    
    /**
     * Get the default dataMap
     *
     * @return Tk_Loader_DataMap
     */
    function getDataMap();

}
