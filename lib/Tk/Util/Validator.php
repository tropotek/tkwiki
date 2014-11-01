<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Validator superclass for form validation
 *
 *
 * @package Tk
 */
abstract class Tk_Util_Validator extends Tk_Object
{
    // POSIX Extended - ereg(), ereg_replace(), split() - D E P R I C A T E D -
    /**
     * Validate an email
     * @match name@domain.com, name-name@domain.com
     * @no-match name@domain, name, *@domain.com
     * @deprecated
     */
    const POSIX_EMAIL = '^[0-9a-zA-Z]([-_.]*[0-9a-zA-Z])*@[0-9a-zA-Z]([-.]?[0-9a-zA-Z])*$';
    
    /**
     * Validate an email
     * @match regexlib.com | this.is.a.museum | 3com.com
     * @no-match notadomain-.com | helloworld.c | .oops.org
     * @deprecated
     */
    const DOMAIN_NAME = '^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$';
    
    /**
     * Check http/https urls with this
     * @match http://www.domain.com
     * @no-match http://domain.com
     * @deprecated
     */
    const POSIX_URL = '^[http(s)?://www.|www.][\S]+$';
    
    /**
     * IP V4 check
     * @match 255.255.255.255
     * @no-match domain.com, 233.233.233.0/24
     * @deprecated
     */
    const POSIX_IPV4 = '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$';
    
    /**
     * Extract flash video urls with this expresion
     * @deprecated
     */
    const POSIX_FLASH_VIDEO = '<embed[^>]*src=\"?([^\"]*)\"?([^>]*alt=\"?([^\"]*)\"?)?[^>]*>';
    
    
    
    
    
    
    /**
     * Validate an email
     * @match name@domain.com, name-name@domain.com
     * @no-match name@domain, name, *@domain.com
     */
    const REG_EMAIL = '/^[0-9a-zA-Z\-\._]*@[0-9a-zA-Z\-]([-.]?[0-9a-zA-Z])*$/';
    
    /**
     * Validate an email
     * @match regexlib.com | this.is.a.museum | 3com.com
     * @no-match notadomain-.com | helloworld.c | .oops.org
     */
    const REG_DOMAIN = '/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/';
    
    /**
     * Check http/https urls with this
     * @match http://www.domain.com
     * @no-match http://domain.com
     */
    const REG_URL = '/^http(s)?:\/\/(www\.)?[\S]+$/';
    // ^http\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(/\S*)?$
    // (http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?
    // ^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&amp;%\$#\=~])*$
    
    
    /**
     * IP V4 check
     * @match 255.255.255.255
     * @no-match domain.com, 233.233.233.0/24
     */
    const REG_IPV4 = '/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/';
    
    /**
     * Extract flash video urls with this expresion
     */
    const REG_FLASH_VIDEO = '/<embed[^>]*src=\"?([^\"]*)\"?([^>]*alt=\"?([^\"]*)\"?)?[^>]*>/i';
    
    
    /**
     * Validate a username
     * @match Name, name@domain.com
     * @no-match *username
     */
    const REG_USERNAME = '/^[a-zA-Z0-9_@ \.\-]+$/i';
    //const REG_USERNAME = '/[^a-z]*/i';
    
    /**
     * Validate a password
     * @match Name, name@domain.com
     * @no-match *username
     */
    const REG_PASSWORD = '/^[a-zA-Z0-9_@ \.\-]+$/i';
    
    
    
    /**
     * @var obj
     */
    protected $obj = null;
    
    /**
     * @var array
     */
    private $errors = null;
    
    /**
     *
     * @param mixed $obj
     */
    function __construct($obj)
    {
        $this->errors = array();
        $this->obj = $obj;
        $this->validate();
    }
    
    /**
     * Implement the validating rules to apply.
     *
     */
    abstract protected function validate();
    
    /**
     * Adds an error message to the array
     *
     * @param string $var
     * @param string $msg
     */
    protected function setError($var, $msg)
    {
        if (!array_key_exists($var, $this->errors)) {
            $this->errors[$var] = array();
        }
        $this->errors[$var][] = $msg;
    }
    
    /**
     * setErrors
     *
     * @param array $errors
     */
    function addErrors($errors)
    {
        foreach ($errors as $name => $msg) {
            if (is_array($msg)) {
                foreach ($msg as $str) {
                    $this->setError($name, $str);
                }
            } else {
                $this->setError($name, $msg);
            }
        }
    }
    
    /**
     * Returns true is string valid, false if not
     *
     * @return boolean
     */
    final function isValid()
    {
        if (count($this->errors) > 0) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Return the error map.
     *
     * @return array
     */
    function getErrors()
    {
        return $this->errors;
    }

}