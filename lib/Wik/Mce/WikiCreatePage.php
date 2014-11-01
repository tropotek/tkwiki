<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * TinyMce Plugin
 * 
 * @package Mce
 */
class Wik_Mce_WikiCreatePage extends Js_Mce_Plugin
{
    
    
    /**
     * Create a new Plugin object
     * 
     * @return Wik_Mce_WikiCreatePage
     */
    static function create()
    {
        $obj = new self('wikiCreatePage');
        return $obj;
    }
    
    /**
     * init
     */
    function init()
    {
        $this->getMce()->addButton($this->getName(), 0, 0);
    }
    
}
