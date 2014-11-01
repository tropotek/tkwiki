<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * 
 *
 * @package Auth
 */
class Wik_Auth_Login extends Auth_Modules_Login
{
    
    
    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();
        
        $this->recoverUrl = Tk_Type_Url::create('/recover.html');
        $this->registerUrl = Tk_Type_Url::create('/register.html');
        
        
    }
    
    
    
    
    
}