<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An user object interface, to be used with the Auth object.
 *
 * @package Com
 */
interface Com_Auth_UserInterface
{
    
    /**
     * Locate a user from a defined source
     *
     * @param string $username
     * @return Com_Auth_UserInterface
     */
    static function findByUsername($username);
    
    /**
     * Get the username
     *
     * @return Com_Auth_LoginInterface
     */
    function getLogin();
    
    /**
     * Get this users group ID
     *
     * @return integer
     */
    function getGroupId();
    
    /**
     * Get the login url
     *
     * @return Tk_Type_Url
     */
    function getLoginUrl();
    
    /**
     * Get the home url
     *
     * @return Tk_Type_Url
     */
    function getHomeUrl();

}