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
class Auth_Event extends Tk_Object
{
    const GROUP_ADMIN   = 64;
    const GROUP_USER    = 8;
    const GROUP_PUBLIC  = 0;
    
    
    
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
     * @var Tk_Type_Url
     */
    protected $loginUrl = null;
    

    
    /**
     * Create the Auth Event object
     *   
     * @param array $permissions
     * @param Tk_Type_Url $loginUrl
     */
    function __construct($permissions = null, $loginUrl = null)
    {
        if (!$permissions) {
            $this->permissions = array (
              '/admin' => self::GROUP_ADMIN,
              '/user' => self::GROUP_USER
            );
        } else {
            $this->permissions = $permissions;
        }
        
        $this->loginUrl = $loginUrl;
        if (!$this->loginUrl) {
            $this->loginUrl = Tk_Type_Url::create('/login.html');
        }
    }
    
    // Override the next three methods if using a different user object than Auth_Db_User
    
    /**
     * Find a user in the system
     * 
     * @param string $username
     * @return Auth_UserInterface
     */
    function createUser($username, $password = '', $groupId = self::GROUP_USER)
    {
        $chk = $this->findByUsername($username);
        if ($chk) {
            throw new Tk_Exception('Username Exists: Please try again with a different username.');
        }
        $user = new Auth_Db_User();
        $user->setUsername($username);
        $user->setGroupId($groupId);
        if (!$password) {
            $password = Auth::createPassword();
        }
        $user->setPassword(Auth::hashPassword($password));
        if (Tk_Config::get('auth.autoActivateUser')) { // Auto activate
            $user->setActive(true);
        }
        $user->save();
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
        return Auth_Db_UserLoader::findByUsername($username, true);
    }
    
    /**
     * Find a user in the system
     * 
     * @param string $hash
     * @return Auth_UserInterface
     */
    function findByHash($hash)
    {
        return Auth_Db_UserLoader::findByHash($hash);
    }
    
    /**
     * Find a user in the DB, may not be active
     * 
     * @param integer $id
     * @return Auth_Db_User
     */
    function findUser($id)
    {
        return Auth_Db_UserLoader::find($id);
    }
    
    
    
    
    
    
    /**
     * Called on successful user login
     * 
     * @param Auth_Db_User $user
     */
    function onLogin($user)
    {
        $user->setLastLogin(Tk_Type_Date::create());
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
        Tk_Type_Url::create('/index.html')->redirect();
    }
    
    /**
     * Get the site title
     * 
     * @return string
     */
    function getSiteTitle()
    {
        return str_replace('www.', '', $_SERVER['HTTP_HOST']);
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
        $admin = Auth_Db_UserLoader::find(1);
        if ($admin) {
            $email = $this->getUserEmail($admin);
            if (preg_match(Tk_Util_Validator::REG_EMAIL, $email)) {
                return $email;
            }
        }
        return 'info@'.$_SERVER['HTTP_HOST'];
    }
    
    /**
     * Get the user email based on a user object
     * 
     * @param Auth_Db_User $user
     * @return string 
     */
    function getUserEmail($user)
    {
        $email = $user->getUsername();
        if (!preg_match(Tk_Util_Validator::REG_EMAIL, $email)) {
            Tk::log('Email not sent, no valid user email address found:' . $email, Tk::LOG_ALERT);
            return '';
        }
        return $email;
    }
    
    
    
    
    
    
    
    function getUserPath($user = null)
    {
        if (!$user) {
            $user = Auth::getUser();
        }
        static $dir = '';
        if ($user && !$dir) {
            foreach ($this->getPermissions() as $k => $v) {
                if ($user->getGroupId() == $v) {
                    $dir = $k;
                    break;
                }
            }
        }
        return $dir;
    }
    
    /**
     * Get the logged in user home page url
     * 
     * @return Tk_Type_Url
     */
    function getUserHome($user = null)
    {
        if (!$user) {
            $user = Auth::getUser();
        }
        return Tk_Type_Url::create($this->getUserPath($user) . '/index.html');
    }
    
    /**
     * Get login URL
     * 
     * @return Tk_Type_Url
     */
    function getLoginUrl()
    {
        return clone $this->loginUrl;
    }
    
    /**
     * Get the page permission array
     * 
     * @return array
     */
    function getPermissions()
    {
        return $this->permissions;
    }
    
    
    
    
    
    
    /**
     * Activate a user account and email the user the account log-in details
     *
     * @param Auth_Db_User $user
     * @param string $siteEmail
     * @param string $siteTitle
     * @return Auth_Db_User
     */
    function onCreate($user, $password)
    {
        $email = $this->getUserEmail($user);
        if (!$email) {
            Tk::log('Create email not sent, no valid email found!', Tk::LOG_ALERT);
            return;
        }
        
        $loginUrl = $this->getLoginUrl()->set('username', $user->getUsername());
        $siteUrl = Tk_Type_Url::create('/');
        
        $address = Tk_Mail_Address::create($email, $this->getSiteEmail());
        
        $message = Tk_Mail_DomMessage::create($address, Tk_Config::get('system.templatePath').'/mail/message.html');
        $message->setSubject('New account request For ' . $this->getSiteTitle());
        
        $str = '';
        if (!Tk_Config::get('auth.autoActivateUser')) {
            $ureg = $this->getLoginUrl()->set('a', $user->getHash())->toString();
            $str = sprintf('<p>
  Your new account has been created. You must activate this account before you can log in:<br/>
  Account Activation URL: <strong><a href="%s">%s</a></strong>
</p>', $ureg, $ureg);
        }
        
        $html = sprintf('
<p>Welcome %s</p>
<p>&#160;</p>

%s

<p>&#160;</p>
<p>
Your new account access details are:<br/>
Login URL: <a href="%s">%s</a><br/>
Username: %s<br/>
Password: %s
</p>

<p>&#160;</p>

        ', $user->getUsername(), $str, $this->getUserHome($user)->toString(), $this->getUserHome($user)->toString(), 
           $user->getUsername(), $password);
        
        $message->setContent($html);
        return $message->send();
    }
    
    
    
    /**
     * Activate a user account and email the user the account log-in details
     *
     * @param Auth_Db_User $user
     * @param string $siteEmail
     * @param string $siteTitle
     * @return Auth_Db_User
     */
    function onActivate($user)
    {
        if ($user->getId() == 1) {
            return;
        }
        $email = $this->getUserEmail($user);
        if (!$email) {
            Tk::log('Activate email not sent, no valid email found!', Tk::LOG_ALERT);
            return;
        }
        
        $loginUrl = $this->getLoginUrl()->set('username', $user->getUsername());
        $siteUrl = Tk_Type_Url::create('/');
        
        $address = Tk_Mail_Address::create($email, $this->getSiteEmail());
        
        $message = Tk_Mail_DomMessage::create($address, Tk_Config::get('system.templatePath').'/mail/message.html');
        $message->setSubject('New Account Approved And Activated For ' . $this->getSiteTitle());
        
        $ureg = $this->getLoginUrl()->set('u', $user->getHash())->toString();
        
        $html = sprintf('
<p>Welcome %s</p>
<p>
  Thank you for joining our online team. Your account has been activated.
</p>

<p>&#160;</p>
<p>
Your new account access details are:<br/>
Login URL: <a href="%s">%s</a><br/>
Username: %s<br/>
</p>
<p>&#160;</p>

<p>
<small>To Unregister at anytime please follow the link: <a href="%s">%s</a></small>
</p>

<p>&#160;</p>

        ', $user->getUsername(), $this->getUserHome($user)->toString(), $this->getUserHome($user)->toString(), 
           $user->getUsername(), $ureg, $ureg);
        
        $message->setContent($html);
        return $message->send();
    }
    
    /**
     * sendEmail
     *
     * @param Auth_Db_User $user
     * @param string $newPass
     * @param string $siteEmail
     * @param string $siteTitle
     * @return Auth_Db_User
     */
    function onChangePassword($user, $newPass)
    {
        $email = $this->getUserEmail($user);
        if (!$email) {
            Tk::log('Change password email not sent, no valid email found!', Tk::LOG_ALERT);
            return;
        }
        $address = Tk_Mail_Address::create($email, $this->getSiteEmail());
        
        $message = Tk_Mail_DomMessage::create($address, Tk_Config::get('system.templatePath').'/mail/message.html');
        $message->setSubject('Password Recovery From ' . $this->getSiteTitle());
        
        $loginUrl = $this->getLoginUrl()->set('username', $user->getUsername());
        $siteUrl = Tk_Type_Url::create('/');
        
        $ureg = $this->getLoginUrl()->set('u', $user->getHash())->toString();
        
        $html = sprintf('
<p>Welcome %s</p>
<p>Please find your account login details below.</p>
<p>&#160;</p>

<p>
Your login details are:<br/>
Username: %s<br/>
Password: %s
</p>
<p>We recommend you change your password to something you are familiar with once you login to your account at <a href="%s">%s</a></p>

<p>&#160;</p>
<p>&#160;</p>
<p>
<small>To Unregister at anytime please follow the link: <a href="%s">%s</a></small>
</p>

<p>&#160;</p>

        ', $user->getUsername(), $user->getUsername(), $newPass, $loginUrl->toString(), $loginUrl->toString(),
           $ureg, $ureg);
        
        $message->setContent($html);
        return $message->send();
    }
    
    
}