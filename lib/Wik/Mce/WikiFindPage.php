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
class Wik_Mce_WikiFindPage extends Js_Mce_Plugin
{
    
    /**
     * Create a new Plugin object
     * 
     * @return Wik_Mce_WikiFindPage
     */
    static function create()
    {
        $obj = new self('wikiFindPage');
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
