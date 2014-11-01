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
interface Com_Auth_LoginInterface
{
    
    /**
     * Get the username
     *
     * @return string
     */
    function getUsername();
    
    /**
     * Get the password
     * This can be pain text or MD% depending on the auth configuration
     *
     * @return string
     */
    function getPassword();

}