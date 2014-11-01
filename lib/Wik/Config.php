<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A config/registry object that configures the Sdk functionality.
 *
 *
 * @package Wik
 */
class Wik_Config extends Com_Config
{
    
    /**
     * Get an instance of this object
     *
     * @return Wik_Config
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Wik_Config();
        }
        return self::$instance;
    }
    
    /**
     * If this is false then users cannot register through the site.
     * An admin of the site will have to create user accounts.
     *
     * @param boolean $b
     * @default true
     */
    static function setUserRegistrationEnabled($b)
    {
        self::set('UserRegistrationEnabled', $b);
    }
    
    /**
     * If this is false then users cannot register through the site.
     * An admin of the site will have to create user accounts.
     *
     * @return boolean
     * @default true
     */
    static function getUserRegistrationEnabled()
    {
        return self::get('UserRegistrationEnabled');
    }
    
    /**
     * Set
     *
     * @param string $str
     * @default
     */
//    function setWikiTemplate($str)
//    {
//        $this->setHtmlTemplates($this->getSitePath() . $str);
//    }
    
    /**
     * Get
     *
     * @return string
     * @default
     */
//    function getWikiTemplate()
//    {
//        return substr($this->getHtmlTemplates(), strlen($this->getSitePath()));
//    }
    

}