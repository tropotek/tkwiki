<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * 
 *
 * @package Mce
 */
class Js_Mce_Plugin_PreElementFix extends Js_Mce_Plugin
{
    
    /**
     * Create a new Filemanager object
     * 
     * @param string $dataPath The root file manager folder
     * @return Js_Mce_Plugin_FileManager
     */
    static function create()
    {
        $obj = new self('preelementfix');
        return $obj;
    }
    
    /**
     * init
     */
    function init()
    {
        
    }
    
    
    
}
