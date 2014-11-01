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
class Com_Auth_BasicController extends Tk_Object implements Tk_Util_ControllerInterface
{
    
    /**
     * The permission array gives access to directories and pages.
     *
     * Usualy defined in the prepend.php or index.php
     * <code>
     * $pagePermissions = array (
     *   '/login.html' => Ext_Db_User::GROUP_PUBLIC, // Default login page
     *   '/admin' => Ext_Db_User::GROUP_ADMIN,
     *   '/user' => Ext_Db_User::GROUP_USER
     * );
     * </code>
     *
     * @var array
     */
    protected $permissions = array();
    
    
    /**
     * __construct
     *
     * @param array $permissions
     */
    function __construct($permissions)
    {
        Tk::log('Initalising Auth Controller', Tk::LOG_INFO);
        
        foreach ($permissions as $k => $v) {
            if (strlen($k) > 1 && substr($k, -1) == '/') {
                $k = substr($k, 0, -1);
            }
            $this->permissions[$k] = $v;
        }
    }
    
    /**
     * Do all pre-initalisation operations
     * This method should be called before the execution method is called
     *
     */
    function init()
    {
        $this->checkAuth();
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
    
    /**
     * Check user permission and restore request data/url.
     *
     */
    function checkAuth()
    {
        $loginUrl = null;
        // Check permissions
        if (!$this->hasPermission()) {
            if (count($this->permissions) > 0) {
                $keys = array_keys($this->permissions);
                $url = current($keys);
                $loginUrl = new Tk_Type_Url($url);
            } else {
                $loginUrl = $this->getAuth()->getUser()->getLoginUrl();
            }
            $loginUrl->redirect();
        }
    }
    
    /**
     * See if the logged in user hass permission to access the page
     *
     * @return boolean
     */
    function hasPermission()
    {
        $permission = $this->getPagePermission(Tk_Request::requestUri());
        if ($permission != 0) {
            if ($this->getAuth()->getUser() == null) {
                return false;
            }
            if ($this->getAuth()->getUser()->getGroupId() >= $permission) {
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
    function getPagePermission(Tk_Type_Url $url)
    {
        $permission = 0;
        $htdocRoot = Com_Config::getHtdocRoot();
        if (substr($htdocRoot, -1) == '/') {
            $htdocRoot = substr($htdocRoot, 0, -1);
        }
        
        $path = str_replace($htdocRoot, '', urldecode($url->getPath()));
        $path = str_replace('//', '/', $path);
        
        if ($path == '/' && array_key_exists($path, $this->permissions)) {
            return $this->permissions[$path];
        }
        
        //while ($path != '.' && $path != '' && !preg_match('/^[A-Za-z]:\$/', $pathname)) {
        while ($path != '.' && $path != '') {
            if (strlen($path) > 1 && substr($path, -1) == '/') {
                $path = substr($path, 0, -1);
            }
            if (array_key_exists($path, $this->permissions)) {
                $permission = $this->permissions[$path];
                break;
            }
            if ($path == '/') {
                break;
            }
            $path = dirname($path);
        }
        return $permission;
    }
    
    /**
     * Get the main auth object
     *
     * @return Com_Auth
     */
    function getAuth()
    {
        return Com_Auth::getInstance();
    }
}