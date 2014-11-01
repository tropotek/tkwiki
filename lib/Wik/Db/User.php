<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Tropotek Development
 */

/**
 *
 *
 * @package Util
 */
class Wik_Db_User extends Tk_Db_Object implements Form_SelectInterface
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
    
    /**
     * @var string
     */
    protected $name = '';
    
    /**
     * @var string
     */
    protected $email = '';
    
    /**
     * @var string
     */
    protected $image = '';
    
    /**
     * @var boolean
     */
    protected $active = true;
    
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
    protected $groupId = self::GROUP_USER;
    
    /**
     * @var string
     */
    protected $hash = '';
    
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
        $this->modified = Tk_Type_Date::createDate();
        $this->created = Tk_Type_Date::createDate();
    }
    
    /**
     * Ensure user id 1 cannot be deleted as it is the admin user
     *
     * @return integer
     */
    function delete()
    {
        if ($this->getId() == 1) {
            return 0;
        }
        if ($this->getImage()) {
            @unlink(Tk_Config::getDataPath() . $this->getImage());
        }
        return parent::delete();
    }
    
    function save()
    {
        $this->hash = Auth::createHash($this);
        return parent::save();
    }
    
    
    
    /**
     * Get the select option value
     * This is commonly the object's ID or index in an array
     *
     * @return mixed
     */
    function getSelectValue()
    {
        return $this->getId();
    }
    
    /**
     * Get the text to show in the select option
     *
     * @return mixed
     */
    function getSelectText()
    {
        return $this->getUsername();
    }
    
    
    /**
     * Required:
     * Range: A string with 128 characters.
     *
     * @return string
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     * Required:
     * Range: A string with 128 characters.
     *
     * @param string $value
     */
    function setName($value)
    {
        $this->name = $value;
    }
    
    /**
     * Required:
     * Range: A string with 128 characters.
     *
     * @return string
     */
    function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Required:
     * Range: A string with 128 characters.
     *
     * @param string $value
     */
    function setEmail($value)
    {
        $this->email = $value;
    }
    
    /**
     * Optional: The user avtar image 120x120
     * Range: A string with 255
     *  characters.
     *
     * @return string
     */
    function getImage()
    {
        return $this->image;
    }
    
    /**
     * Optional: The user avtar image 120x120
     * Range: A string with 255
     *  characters.
     *
     * @param string $value
     */
    function setImage($value)
    {
        $this->image = $value;
    }
    
    /**
     * Get the image Url object
     *
     * @return Tk_Type_Url
     */
    function getImageUrl()
    {
        return Tk_Type_Url::createDataUrl($this->getImage());
    }
    
    /**
     * If the user is inactive they cannot login
     * Range: true or false
     *
     * @return boolean
     */
    function getActive()
    {
        return $this->active;
    }
    
    /**
     * If the user is inactive they cannot login
     * Range: true or false
     *
     * @param boolean $b
     */
    function setActive($b)
    {
        $this->active = $b;
    }
    
    /**
     * Required:
     * Range: A string with 64 characters.
     *
     * @return string
     */
    function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Required:
     * Range: A string with 64 characters.
     *
     * @param string $value
     */
    function setUsername($value)
    {
        $this->username = $value;
    }
    
    /**
     * Required:
     * Range: A string with 64 characters.
     *
     * @return string
     */
    function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Required:
     * Range: A string with 64 characters.
     *
     * @param string $value
     */
    function setPassword($value)
    {
        $this->password = $value;
    }
    
    /**
     * Required: ADMIN = 128, USER = 1
     * Safe Range: A normal-size
     *  integer. The signed range is -2147483648 to 2147483647. The unsigned
     *  range is 0 to 4294967295.
     *
     * @return integer
     */
    function getGroupId()
    {
        return $this->groupId;
    }
    
    /**
     * Required: ADMIN = 128, USER = 1
     * Safe Range: A normal-size
     *  integer. The signed range is -2147483648 to 2147483647. The unsigned
     *  range is 0 to 4294967295.
     *
     * @param integer $i
     */
    function setGroupId($i)
    {
        $this->groupId = $i;
    }
    
    /**
     * Used by the user activation system
     * Range: A string with 64
     *  characters.
     *
     * @return string
     */
    function getHash()
    {
        return $this->hash;
    }
    
    /**
     * Used by the user activation system
     * Range: A string with 64
     *  characters.
     *
     * @param string $value
     */
    function setHash($value)
    {
        $this->hash = $value;
    }
    
    /**
     * Get modified
     * Range: '1000-01-01 00:00:00' to '9999-12-31
     *  23:59:59'. DB values in 'YYYY-MM-DD HH:MM:SS' format.
     *
     * @return Tk_Type_Date
     */
    function getModified()
    {
        return $this->modified;
    }
    
    /**
     * Get created
     * Range: '1000-01-01 00:00:00' to '9999-12-31
     *  23:59:59'. DB values in 'YYYY-MM-DD HH:MM:SS' format.
     *
     * @return Tk_Type_Date
     */
    function getCreated()
    {
        return $this->created;
    }
    
    
    
    
}

/**
 * A validator object for the Wik_Db_User object
 *
 * @package Util
 */
class Wik_Db_UserValidator extends Tk_Util_Validator
{
    
    /**
     * @var Wik_Db_User
     */
    protected $obj = null;
    
    /**
     * Validates
     *
     */
    function validate()
    {
        
        if (!preg_match('/^.{1,128}$/', $this->obj->getName())) {
            $this->setError('name', 'Invalid Field Value.');
        }
//        if (!preg_match(self::REG_EMAIL, $this->obj->getEmail())) {
//            $this->setError('username', 'Invalid email Value.');
//        }
        $path = Tk_Type_Path::getFileExtension($this->obj->getImage());
        if ($this->obj->getImage() != null && ($path != '' && $path != 'jpeg' && $path != 'jpg' && $path != 'gif' && $path != 'png')) {
            $this->setError('image', 'Invalid file type.');
        }

        if (!preg_match(self::REG_EMAIL, $this->obj->getUsername())) {
            $this->setError('username', 'Invalid Username Value, please use a valid email address.');
        }
        if (!preg_match('/^[a-z0-9_\. @-]{4,64}$/i', $this->obj->getPassword())) {
            $this->setError('password', 'Invalid Password Value. (Must be more than 4 characters.)');
        }
        if ($this->obj->getGroupId() <= Wik_Auth_Event::GROUP_PUBLIC || $this->obj->getGroupId() > Wik_Auth_Event::GROUP_ADMIN) {
            $this->setError('groupId', 'Invalid Group ID Value.');
            throw new Tk_ExceptionIllegalArgument('Invalid group ID Assignment.');
        }
        
        $chk = Wik_Db_UserLoader::findByUsername($this->obj->getUsername());
        if ($chk) {
            if ($this->obj->getId()) {
                if ($chk->getId() != $this->obj->getId()) {
                    $this->setError('username', 'Username used for an existing user.');
                }
            } else {
                $this->setError('username', 'Username used for an existing user.');
            }
        }
        
    
    }
    

}