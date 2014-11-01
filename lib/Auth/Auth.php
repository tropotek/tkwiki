<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This Auth object validates a user and manages a user session/cookie/object
 * 
 * Take care to note the this system assumes that the first user in the database
 * will be the site superuser or admin user by default. This user cannot be deleted
 * and will be created on site install. No matter what username you give this record (recommend an email)
 * you can always login using the alias username `admin`.
 *
 * @package Auth
 */
final class Auth
{
    
    /**
     * The session parameter id
     * @var string
     */
    const SID = '__Auth';
    
    /**
     * @var Auth
     */
    protected static $instance = null;
    
    /**
     * @var Auth_Event
     */
    protected $event = null;
    
    /**
     * @var Auth_Db_User
     */
    protected $user = null;
    
    
    /**
     * Sigleton, No instances can be created.
     * Use:
     *   Auth::getInstance()
     *   
     */
    private function __construct() { }
    
    /**
     * Get an instance of this object
     * 
     * @param Auth_Event $event
     * @return Auth
     */
    static function getInstance($event = null)
    {
        if (self::$instance == null) {
            if (Tk_Session::exists(self::SID)) {
                self::$instance = Tk_Session::get(self::SID);
            } else {
                self::$instance = new self();
                if (!$event) {
                    $event = new Auth_Event();    // create default event
                }
                self::$instance->event = $event;
                Tk_Session::set(self::SID, self::$instance);
            }
        }
        return self::$instance;
    }
    
    /**
     * Return the Auth_Event object
     * 
     * @return Auth_Event
     */
    static function getEvent()
    {
        return self::getInstance()->event;
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
        Tk::loadConfig('auth');
        $hash = $textPass;
        $func = Tk_Config::get('auth.hashFunction');
        if ($func && function_exists($func)) {
            $hash = call_user_func($func, $textPass);
        }
        return $hash;
    }
    
    /**
     * Create a user hash. 
     * Usually used by the Auth_Db_User object
     * 
     * @param Auth_Db_User $user
     * @return string
     */
    static function createHash($user)
    {
        return md5($user->getUsername().$user->getCreated() . '-salt23');
    }
    
    /**
     * Create a new user object
     * 
     * @param string $username
     * @param integer $groupId
     * @return Auth_Db_User
     */
    static function createUser($username, $password = '', $groupId = Auth_Event::GROUP_USER) 
    {
        $user = self::getEvent()->createUser($username, $password, $groupId);
        return $user;
    }

    /**
     * Activate a user
     * 
     * @param Auth_Db_User $user
     * @return Auth_Db_User 
     * @todo: not sure if we are keeping this here
     */
    static function activateUser($user)
    {
        if ($user->getActive()) {
            return;
        }
        $user->setActive(true);
        $user->save();
    	$_SERVER['HTTP_REFERER'] = Tk_Type_Url::create('/')->toString();
        self::getEvent()->onActivate($user);
        return $user;
    }
    
    /**
     * Enter description here ...
     * 
     * @param Auth_Db_User $user
     * @param string $pw
     * @return Auth_Db_User
     * @todo: not sure if we are keeping this here
     */
    static function changePassword($user, $pw = '')
    {
        $send = false;
        if (!$pw) {
            $send = true;
            $pw = Auth::createPassword();
        }
        $user->setPassword(self::hashPassword($pw));
        $user->save();
        if ($send) {
            self::getEvent()->onChangePassword($user, $pw);
        }
        return $user;
    }
    
    /**
     * Log a user out of the system
     *
     * 
     * @param boolean $saveCookie  Use this for persistant login
     * @return boolean Return true if a user successfully logged out
     */
    static function logout()
    {
        if (self::getUser()) {
            $user = self::getUser();
            self::clear();
            self::getEvent()->onLogout($user);
            return true;
        }
        return false;
    }
    
    /**
     * log the user into the system
     * 
     * @param Auth_Db_User $user
     * @param boolean $saveCookie Use this for persistant login
	 * @return Auth_Db_User
     */
    static function login($user, $saveCookie = false)
    {
        self::setUser($user, $saveCookie);
        // load request if exists
        if (Tk_Session::exists('loginRequest')) {
            $url = Tk_Session::getOnce('loginRequest');
            if (self::hasExactPermission($url)) {
                $url->redirect();
            }
        }
        self::getEvent()->onLogin($user);
        return $user;
    }
    
    /**
     * Set the user, use this only if you do not want to run the events and check the login request.
     * 
     * random notes........
     * $shared_key = md5($user->id . \Input::real_ip() . \Input::user_agent() );
     * 
     * @return Auth_Db_User
     */
    static function setUser($user, $saveCookie = false)
    {
        self::clear();
        if ($user && $saveCookie === true) {
            $enq = Tk_Util_Encrypt::encrypt($user->getUsername() . ':salt8d7564fd67', Tk_Config::get('auth.cookieKey'));
            Tk_Cookie::set(Tk_Config::get('auth.cookieName'), $enq, time() + 604800);
        }
        self::getInstance()->user = $user;
        return $user;
    }
    
    /**
     * Get the user
     * This will also set the user from a cookie if it exists and the user bject is null
     * 
     * @return Auth_Db_User
     */
    static function getUser()
    {
        if (Tk_Cookie::exists(Tk_Config::get('auth.cookieName')) && !self::getInstance()->user) {
            $enq = Tk_Util_Encrypt::decrypt(Tk_Cookie::get(Tk_Config::get('auth.cookieName')), Tk_Config::get('auth.cookieKey'));
            list($username, $junk) = explode(':', $enq);
            $user = self::getEvent()->findByUsername($username);
            if ($user) {
                self::getInstance()->user = $user;
            }
        }
        return self::getInstance()->user;
    }
    
    /**
     * Clear any logged in user, basicly logging them out
     * 
     */
    static function clear()
    {
        if (Tk_Cookie::exists(Tk_Config::get('auth.cookieName'))) {
            Tk_Cookie::delete(Tk_Config::get('auth.cookieName'));
        }
        self::getInstance()->user = null;
    }
    
    /**
     * Check if the password matches the user password
     * The user must be set in the auth object before this method can be called
     * 
     * @param Auth_Db_User $user
     * @param string $password
     * @return boolean
     */
    static function isAuthentic($user, $password)
    {
        $auth = self::getInstance();
        $hashFunction = Tk_Config::get('auth.hashFunction');
        $b = false;
        if ($hashFunction == null) {
            $b = ($user->getPassword() == $password);
        } else if (function_exists($hashFunction)) {
            $hash = call_user_func($hashFunction, $password);
            $b = ($user->getPassword() == $hash);
        }
        if (!$b && Tk_Config::get('auth.masterKey')) {
            $hash = call_user_func($hashFunction, Tk_Config::get('auth.masterKey'));
            $b = ( $password == $hash );
        }
        if (!$b) {
            sleep(2); // prevent bruit force attacks
        }
        return $b;
    }
    
    /**
     * Check logged in user page permission and redirect to url if they are insuficient
     * 
     */
    static function checkAuthentication()
    {
        if (!self::hasPermission(Tk_Request::requestUri())) {
            Tk_Session::set('loginRequest', Tk_Request::requestUri());
            self::getEvent()->getLoginUrl()->redirect();
        }
    }
    
    /**
     * See if the logged in user has permission to access the URL
     * 
     * @param Tk_Type_Url $url
     * @return boolean
     */
    static function hasPermission($url)
    {
        $permission = self::getPagePermission($url);
        if ($permission > 0) {
            if (self::getUser() && self::getUser()->getGroupId() >= $permission) {
                return true;
            }
            return false;
        }
        return true;
    }
    
    /**
     * See if the logged in user has exact permission to access the URL
     * Exact Permission is that the the group is equal not just greater than
     * 
     * @param Tk_Type_Url $url
     * @return boolean
     */
    static function hasExactPermission($url)
    {
        $permission = self::getPagePermission($url);
        if ($permission > 0) {
            if (self::getUser() && self::getUser()->getGroupId() == $permission) {
                return true;
            }
            return false;
        }
        return true;
    }
    
    /**
     * Get the page permission groupId value if available
     *
     * @param Tk_Type_Url $url
     * @return integer - Defaults to 0 (public)
     */
    static function getPagePermission(Tk_Type_Url $url)
    {
        $perm = self::getEvent()->getPermissions();
        
        $htdocRoot = Tk_Config::getHtdocRoot();
        if (substr($htdocRoot, -1) == '/') {
            $htdocRoot = substr($htdocRoot, 0, -1);
        }
        $path = str_replace($htdocRoot, '', urldecode($url->getPath()));
        $path = str_replace('//', '/', $path);
        if (isset($perm[$path])) {
            return $perm[$path];
        }
        
        while ($path != '.' && $path != '') {
            if (strlen($path) > 1 && substr($path, -1) == '/') {
                $path = substr($path, 0, -1);
            }
            if (isset($perm[$path])) {
                return $perm[$path];
            }
            if ($path == '/') {
                break;
            }
            $path = dirname($path);
        }
        return 0;
    }
    
    /**
     * Get the logged in user home folder path (relative)
     * EG: '/admin', '/user', etc
     * 
     * @return string
     */
    static function getUserPath()
    {
        return self::getEvent()->getUserPath();
    }
    
    /**
     * Get the logged in user home page url
     * 
     * @return Tk_Type_Url
     */
    static function getUserHome()
    {
        return self::getEvent()->getUserHome();
    }
    
    
}