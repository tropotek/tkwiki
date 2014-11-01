<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An user object interface, to be used with the Auth object.
 * 
 * It is assumed throughout this object that the username is a valid email.
 * To change this subclass this object and refactor it to suit your needs.
 * 
 *
 * @package Auth
 */
class Wik_Auth_Event extends Auth_Event
{
    
    /**
     * No login required
     */
    const GROUP_PUBLIC = 0;
    /**
     * User permissions required
     */
    const GROUP_USER = 1;
    /**
     * Admin permissions required
     */
    const GROUP_ADMIN = 128;
    
    
    
    // Override the next three methods if using a different user object than Auth_Db_User
    
    /**
     * Find a user in the system
     * 
     * @param string $username
     * @return Auth_UserInterface
     */
    function createUser($username, $password = '', $groupId = self::GROUP_USER)
    {
        $user = new Wik_Db_User();
        $user->setUsername($username);
        $user->setEmail($username);
        $user->setGroupId($groupId);
        $name = substr($username, 0, strpos($username, '@'));
        $user->setName($name);
        $user->setHash(Auth::createHash($user));
        if (!$password) {
            $password = Auth::createPassword();
        }
        $user->setPassword(Auth::hashPassword($password));
        $this->onCreate($user, $password);
        return $user;
    }
    
    /**
     * Find a user in the system
     * 
     * @param string $username
     * @return Auth_UserInterface
     */
    function findByUsername($username)
    {
        if ($username == 'admin') {  // allow alias username for the first user in the DB
            return self::findUser(1);
        }
        return Wik_Db_UserLoader::findByUsername($username);
    }
    
    /**
     * Find a user in the system
     * 
     * @param string $hash
     * @return Auth_UserInterface
     */
    function findByHash($hash)
    {
        return Wik_Db_UserLoader::findByHash($hash);
    }
    
    /**
     * Find a user in the DB, may not be active
     * 
     * @param integer $id
     * @return Auth_Db_User
     */
    function findUser($id)
    {
        return Wik_Db_UserLoader::find($id);
    }
    
    
    /**
     * Get the site title
     * 
     * @return string
     */
    function getSiteTitle()
    {
        return Wik_Db_Settings::getInstance()->getTitle();
    }

    /**
     * Get the site email
     * First the code tries to get the email of the admin user
     * if that is not a valid email then it returns info@{HTTP_HOST}
     * 
	 * @return string
     */
    function getSiteEmail()
    {
        return Wik_Db_Settings::getInstance()->getSiteEmail();
    }
    
    /**
     * Get the user email based on a user object
     * 
     * @param Auth_Db_User $user
     * @return string 
     */
    function getUserEmail($user)
    {
        if ($user) {
            return $user->getEmail();
        }
    }
    
    /**
     * Get the logged in user home page url
     * 
     * @return Tk_Type_Url
     */
    function getUserHome($user = null)
    {
        return Tk_Type_Url::create('/index.html');
    }
    
    
    
    /**
     * Called on successful user login
     * 
     * @param Auth_Db_User $user
     */
    function onLogin($user)
    {
        //$user->setLastLogin(Tk_Type_Date::create());
        $user->save();
        $this->getUserHome()->redirect();
    }
    
    /**
     * Called on successful user logout
     * 
     * @param Auth_Db_User $user
     */
    function onLogout($user)
    {
        $url = new Tk_Type_Url('/index.html');
        $url->redirect();
    }
}