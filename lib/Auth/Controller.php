<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A front controller for user authentication
 *
 * @package Com
 */
class Auth_Controller extends Tk_Object implements Tk_Util_ControllerInterface
{
    
    /**
     * __construct
     *
     * @param Auth_Event $event
     */
    function __construct(Auth_Event $event)
    {
        // Int the auth object. 
        // If not using the controllers this must be done somewhere else in your code.... 
        Auth::getInstance($event);
    }
    
    /**
     * Do all pre-initalisation operations
     * This method should be called before the execution method is called
     *
     */
    function init()
    {
        Auth::checkAuthentication();
    }
    
    /**
     * Process the request and response of page requested
     *
     */
    function execute()
    {
    }
    
    /**
     * Do all post initalisation operations here
     * This function should be called after the execute method has been called
     *
     */
    function postInit()
    {
    }
    
}