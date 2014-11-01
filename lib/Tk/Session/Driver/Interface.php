<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A session object
 *
 * @package Tk
 */
interface Tk_Session_Driver_Interface
{
    
    /**
     * Opens a session.
     *
     * @param   string $path  save path
     * @param   string $name  session name
     * @return  boolean
     */
    function open($path, $name);
    
    /**
     * Closes a session.
     *
     * @return  boolean
     */
    function close();
    
    /**
     * Reads a session.
     *
     * @param   string $id session id
     * @return  string
     */
    function read($id);
    
    /**
     * Writes a session.
     *
     * @param   string $id  session id
     * @param   string $data  session data
     * @return  boolean
     */
    function write($id, $data);
    
    /**
     * Destroys a session.
     *
     * @param   string $id  session id
     * @return  boolean
     */
    function destroy($id);
    
    /**
     * Regenerates the session id.
     *
     * @return  string
     */
    function regenerate();
    
    /**
     * Garbage collection.
     *
     * @param   integer $maxlifetime session expiration period
     * @return  boolean
     */
    function gc($maxlifetime);

}

