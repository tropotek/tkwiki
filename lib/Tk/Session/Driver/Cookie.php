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
class Tk_Session_Driver_Cookie implements Tk_Session_Driver_Interface
{
    /**
     * @var string
     */
    protected $cookieName = '';
    
    protected $encrypt = false;
    
    protected $sessionCfg = null;
    
    
    function __construct()
    {
        $this->sessionCfg = Tk::loadConfig('Tk', 'session');
        $this->cookieName = $this->sessionCfg['name'] . '_data';
        $this->encrypt = $this->sessionCfg['encryption'];
        Tk::log('Session Cookie Driver Initialized');
    }
    
    function open($path, $name)
    {
        return true;
    }
    
    function close()
    {
        return true;
    }
    
    function read($id)
    {
        $data = (string)Tk_Cookie::get($this->cookieName);
        if ($data == '') {
            return $data;
        }
        return empty($this->encrypt) ? base64_decode($data) : Tk_Util_Encrypt::decrypt($data);
    }
    
    function write($id, $data)
    {
        $data = empty($this->encrypt) ? base64_encode($data) : Tk_Util_Encrypt::encrypt($data);
        
        if (strlen($data) > 4048) {
            Tk::log('Session (' . $id . ') data exceeds the 4KB limit, ignoring write.', Tk::LOG_ERROR);
            return false;
        }
        return Tk_Cookie::set($this->cookieName, $data, $this->sessionCfg['expiration']);
    }
    
    function destroy($id)
    {
        return Tk_Cookie::delete($this->cookieName);
    }
    
    function regenerate()
    {
        session_regenerate_id(true);
        // Return new id
        return session_id();
    }
    
    function gc($maxlifetime)
    {
        return true;
    }

}