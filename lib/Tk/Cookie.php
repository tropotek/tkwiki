<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This object handles the all Cookie requests.
 *
 * @package Tk
 */
class Tk_Cookie extends Tk_Object
{
    
    /**
     * @var Tk_Cookie
     */
    protected static $instance = null;
    
    
    
    
    /**
     * Sigleton, No instances can be created.
     * Use:
     *   Tk_Cookie::getInstance()
     */
    private function __construct()
    {
        //Tk::loadConfig('tk.cookie');
    }
    
    /**
     * Get an instance of this object
     *
     * @return Tk_Cookie
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Returns true if there is a cookie with this name.
     *
     * @param string $name
     * @return boolean
     */
    function parameterExists($name)
    {
        return isset($_COOKIE[$name]);
    }
    
    /**
     * Get the value of the given cookie. If the cookie does not exist null will be returned.
     *
     * @param string $name
     * @param string $default
     * @return mixed
     */
    function getParameter($name)
    {
        return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : null);
    }
    
    /**
     * Set a cookie. Silently does nothing if headers have already been sent.
     *
     * @param string $name
     * @param string $value
     * @param integer $expire a timestamp when the cookie will expire
     * @return bool
     */
    function setParameter($name, $value, $expire = null)
    {
        $retval = false;
        if (!headers_sent()) {
            if ($expire === null) {
                $expire = Tk_Config::get('tk.cookie.expire') + time();
            }
            $retval = null;
            //vd(Tk_Config::get('tk.cookie.path'), Tk_Config::get('tk.cookie.domain'), Tk_Config::get('tk.cookie.secure'), Tk_Config::get('tk.cookie.httponly'));
            if (version_compare(PHP_VERSION, '5.2.0', '>')) {
                $retval = @setcookie($name, $value, $expire, Tk_Config::get('tk.cookie.path'), Tk_Config::get('tk.cookie.domain'), Tk_Config::get('tk.cookie.secure'), Tk_Config::get('tk.cookie.httponly'));
            } else {
                $retval = @setcookie($name, $value, $expire, Tk_Config::get('tk.cookie.path'), Tk_Config::get('tk.cookie.domain'), Tk_Config::get('tk.cookie.secure'));
            }
            if ($retval) {
                $_COOKIE[$name] = $value;
            }
        }
        return $retval;
    }
    
    /**
     * Delete a cookie.
     *
     * @param string $name
     * @param Tk_Type_Path $path
     * @param string $domain
     * @param bool $removeGlobal Set to true to remove cookie from this request.
     * @return bool
     */
    function removeParameter($name, $removeGlobal = true)
    {
        $retval = false;
        if (!headers_sent()) {
            if (version_compare(PHP_VERSION, '5.2.0', '>')) {
                $retval = setcookie($name, '', time()-3600, Tk_Config::get('tk.cookie.path'), Tk_Config::get('tk.cookie.domain'), Tk_Config::get('tk.cookie.secure'), Tk_Config::get('tk.cookie.httponly'));
            } else {
                $retval = setcookie($name, '', time()-3600, Tk_Config::get('tk.cookie.path'), Tk_Config::get('tk.cookie.domain'), Tk_Config::get('tk.cookie.secure'));
            }
            if ($removeGlobal) {
                unset($_COOKIE[$name]);
            }
        }
        return $retval;
    }

    
    
    

    
    /**
     * Returns true if there is a cookie with this name.
     *
     * @param string $key
     * @return boolean
     */
    static function exists($key)
    {
        return self::getInstance()->parameterExists($key);
    }
    
    /**
     * Get the value of the given cookie. If the cookie does not exist null will be returned.
     *
     * @param string $key
     * @return mixed
     */
    static function get($key)
    {
        return (isset($_COOKIE[$key]) ? $_COOKIE[$key] : null);
    }
    
    /**
     * Set a cookie. Silently does nothing if headers have already been sent.
     *
     * @param string $key
     * @param string $value
     * @param integer $expire
     * @return bool
     */
    static function set($key, $value, $expire = null)
    {
        return self::getInstance()->setParameter($key, $value, $expire);
    }
    
    /**
     * Delete a cookie.
     *
     * @param string $key
     * @param bool $removeGlobal Set to true to remove cookie from this request.
     * @return bool
     */
    static function delete($key, $removeGlobal = true)
    {
        return self::getInstance()->removeParameter($key, $removeGlobal);
    }
    
}