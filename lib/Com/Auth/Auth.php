<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This Auth object validates a user and manages a user session/cookie/object
 *
 * @package Com
 */
class Com_Auth extends Tk_object
{
    
    /**
     * @var Com_Auth
     */
    protected static $instance = null;
    
    /**
     * The session parameter id
     */
    const SID = 'Com_Auth';
    
    /**
     * @var Com_Auth_UserInterface
     */
    private $user = null;
    
    
    /**
     * Sigleton, No instances can be created.
     * Use:
     *   Com_Auth::getInstance()
     */
    private function __construct()
    {
    }
    
    /**
     * Get an instance of this object
     *
     * @return Com_Auth
     */
    static function getInstance()
    {
        if (Tk::moduleExists('Auth')) {
            vd('do not use!');
        }
        Tk::loadConfig('com.auth');
        if (self::$instance == null) {
            if (Tk_Session::exists(self::SID)) {
                self::$instance = Tk_Session::get(self::SID);
            } else {
                self::$instance = new self();
                Tk_Session::set(self::SID, self::$instance);
            }
        }
        return self::$instance;
    }
    
    
    /**
     * Find a user in the system
     *
     * @param string $username
     * @return Com_Auth_UserInterface
     */
    static function findUser($username)
    {
        if (!class_exists(Com_Config::get('com.auth.userClass'))) {
            return;
        }
        Tk::loadConfig('com.auth');
        if (!class_exists(Com_Config::get('com.auth.userClass'))) {
            throw new Tk_ExceptionIllegalArgument('Auth User Class Not Found: `' . Com_Config::get('com.auth.userClass') . '`');
        }
        $user = eval('return ' . Com_Config::get('com.auth.userClass') . "::findByUsername('$username');");
        if ($username == 'admin') {
            $user = eval('return ' . Com_Config::get('com.auth.userClass') . "Loader::find(1);");
        }
        return $user;
    }
    
    /**
     * Create a random password
     *
     * @param integer $length
     * @return string
     */
    static function createPassword($length = 8)
    {
        if (Tk_Config::isDebugMode()) {
            return 'password';
        }
        //$chars = "234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $chars = '234567890abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $i = 0;
        $password = '';
        while ($i <= $length) {
            $password .= $chars{mt_rand(0, strlen($chars) - 1)};
            $i++;
        }
        return $password;
    }
    
    /**
     * Hash a text password to the system required password hash
     * If no function is defined then the original $textPass is returned.
     *
     * @param string $textPass
     * @return string
     */
    static function hashPassword($textPass)
    {
        Tk::loadConfig('com.auth');
        $hash = $textPass;
        $func = Com_Config::get('com.auth.hashFunction');
        if ($func && function_exists($func)) {
            $com = 'return ' . $func . '(\'' . $textPass . '\');';
            $hash = eval($com);
        }
        return $hash;
    }
    
    /**
     * Set the user
     * If null no user is logged in
     *
     * @param Com_Auth_UserInterface $user
     * @param boolean $saveCookie
     * @throws Tk_ExceptionIllegalArgument
     */
    function setUser($user, $saveCookie = false)
    {
        if (!class_exists(Com_Config::get('com.auth.userClass'))) {
            return;
        }
        if ($user && !$user instanceof Com_Auth_UserInterface) {
            throw new Tk_ExceptionIllegalArgument('User object must implement Com_Auth_UserInterface');
        }
        if ($user instanceof Com_Auth_UserInterface) {
            if ($saveCookie === true) {
                $enq = Tk_Util_Encrypt::encrypt($user->getLogin()->getUsername() . ':salt8d7564fd67', Com_Config::get('com.auth.cookieKey'));
                Tk_Cookie::set(Com_Config::get('com.auth.cookieName'), $enq, time() + 604800);
            }
            $this->user = $user;
        } else {
            Tk_Cookie::delete(Com_Config::get('com.auth.cookieName'));
            $this->user = null;
        }
    }
    
    /**
     * Get the user
     * This will also set the user from a cookie if it exists and the user bject is null
     *
     * @return Com_Auth_UserInterface
     */
    function getUser()
    {
        if (!class_exists(Com_Config::get('com.auth.userClass'))) {
            return;
        }
        if (Tk_Cookie::exists(Com_Config::get('com.auth.cookieName')) && $this->user == null) {
            $enq = Tk_Util_Encrypt::decrypt(Tk_Cookie::get(Com_Config::get('com.auth.cookieName')), Com_Config::get('com.auth.cookieKey'));
            list($username, $junk) = explode(':', $enq);
            $user = self::findUser($username);
            if ($user) {
                $this->setUser($user);
            }
        }
        return $this->user;
    }
    
    /**
     * A static alias function for getUser()
     * 
     * @return Com_Auth_UserInterface
     */
    static function user()
    {
        return self::getInstance()->getUser();
    }
    
    /**
     * Get the user home folder path (relative)
     * 
     * @return string
     */
    static function userPath()
    {
        return dirname(self::user()->getHomeUrl()->toUriString());
    }
    
    /**
     * get the user home page url
     * 
     * @return Tk_Type_Url
     */
    static function userHomeUrl()
    {
        return self::user()->getHomeUrl();
    }
    
    
    
    /**
     * Check if the password matches the user password
     *
     * The user must be set in the auth object before this method can be called
     *
     * @param string $str
     * @param string $hashFunction Eg: 'md5', 'sha1', etc Default is plain text
     * @note: The master key is not available for opensource projects.
     */
    function isAuthentic($str)
    {
        $hashFunction = Com_Config::get('com.auth.hashFunction');
        $b = false;
        if ($this->user == null || $this->user->getLogin() == null) {
            throw new Tk_ExceptionNullPointer('No user object to authenticate.');
        }
        if ($hashFunction == null) {
            $b = ($this->user->getLogin()->getPassword() == $str);
        } else if (function_exists($hashFunction)) {
            $hash = @eval('return ' . $hashFunction . "('" . $str . "');");
            $b = ($this->user->getLogin()->getPassword() == $hash);
        }
        if (!$b && Com_Config::get('com.auth.masterKey')) {
            $b = ($str == Com_Config::get('com.auth.masterKey'));
        }
        return $b;
    }

}