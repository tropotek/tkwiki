<?php
/*       -- TkLib Auto Class Builder --
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Tropotek Development
 */

/**
 * User with the ID of 1 is assumed to be the site admin 
 *   login and cannot be deleted
 *
 * @package Db
 */
class Auth_Db_User extends Tk_Db_Object  implements Form_SelectInterface
{
    
    
    /**
     * @var string
     */
    protected $username = '';
    
    /**
     * @var string
     */
    protected $password = '';
    
    /**
     * @var integer
     */
    protected $groupId = 0;
    
    /**
     * @var boolean
     */
    protected $active = false;
    
    /**
     * @var string
     */
    protected $hash = '';
    
    /**
     * @var Tk_Type_Date
     */
    protected $lastLogin = null;
    
    /**
     * @var Tk_Type_Date
     */
    protected $modified = null;
    
    /**
     * @var Tk_Type_Date
     */
    protected $created = null;
    
    
    
    /**
     * __construct
     *
     */
    function __construct()
    {
        $this->modified = Tk_Type_Date::create();
        $this->created = Tk_Type_Date::create();
        
    }
    
    
    
    /**
     * Return the option label
     *
     * @return string
     */
    function getSelectText()
    {
        return $this->getUsername();
    }
    
    /**
     * Return the option value
     *
     * @return string
     */
    function getSelectValue()
    {
        return $this->getId();
    }
    
    
    /**
     * save
     */
    function save()
    {
        if ($this->getId() == 1) {
            $this->active = true;
        }
        $this->hash = Auth::createHash($this);
        return parent::save();
    }
    
    /**
     * insert
     */
    function insert()
    {
        $ret = parent::insert();
        if (Tk_Config::get('auth.autoActivateUser')) {
            Auth::activateUser($this);
        }
        return $ret;
    }
    
    /**
     * delete
     */
    function delete()
    {
        if ($this->getId() == 1) {
            return;
        }
        return parent::delete();
    }
    
    
    
    
    
    
    
    /**
     * It is recommended that you use a valid email for a username 
     * however not required. If you do not use an email you will need
     * to override the Auth_Event functions to get the user email
     * from another location
     * 
     * @return string
     */
    function getUsername()
    {
        return $this->username;
    }
    
    /**
     *
     * @param string $value
     */
    function setUsername($value)
    {
        $this->username = $value;
    }
    
    /**
     * Required:
     *
     * @return string
     */
    function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Required:
     *
     * @param string $value
     */
    function setPassword($value)
    {
        $this->password = $value;
    }
    
    /**
     * 
     * @return integer
     */
    function getGroupId()
    {
        return $this->groupId;
    }
    
    /**
     * 
     * @param integer $i
     */
    function setGroupId($i)
    {
        $this->groupId = $i;
    }
    
    /**
     * Get active
     *
     * @return boolean
     */
    function getActive()
    {
        return $this->active;
    }
    
    /**
     * Set active
     *
     * @param boolean $b
     */
    function setActive($b)
    {
        $this->active = $b;
    }
    
    /**
     * Used by the user activation system
     *
     * @return string
     */
    function getHash()
    {
        return $this->hash;
    }
    
    /**
     * Used by the user activation system
     *
     * @param string $value
     */
    function setHash($value)
    {
        $this->hash = $value;
    }
    
    /**
     * Get lastLogin
     *
     * @return Tk_Type_Date
     */
    function getLastLogin()
    {
        return $this->lastLogin;
    }
    
    /**
     * Set lastLogin
     *
     * @param Tk_Type_Date $date
     */
    function setLastLogin($date)
    {
        $this->lastLogin = $date;
    }
    
    /**
     * Get modified
     *
     * @return Tk_Type_Date
     */
    function getModified()
    {
        return $this->modified;
    }
    
    /**
     * Get created
     *
     * @return Tk_Type_Date
     */
    function getCreated()
    {
        return $this->created;
    }
    


}

/**
 * A validator object for `Auth_Db_User`
 *
 * @package Db
 */
class Auth_Db_UserValidator extends Tk_Util_Validator
{

    /**
     * @var Auth_Db_User
     */
    protected $obj = null;

    /**
     * Validates
     *
     */
    function validate()
    {
        if (!preg_match(self::REG_USERNAME, $this->obj->getUsername())) {
            $this->setError('username', 'Invalid characters used in username');
        }
        $chk = Auth_Db_UserLoader::findByUsername($this->obj->getUsername());
        if ($chk) {
            if ($this->obj->getId() == 0) {
                $this->setError('username', 'A user already exists with selected username.');
            } else {
                if ($this->obj->getId() != $chk->getId()) {
                    $this->setError('username', 'A user already exists with selected username.');
                }
            }
        }
    }
    
    
}