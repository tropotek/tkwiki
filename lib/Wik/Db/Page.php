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
class Wik_Db_Page extends Tk_Db_Object
{
    const ACCESS_NONE = 0;
    const ACCESS_ALL = 7;
    
    const ACCESS_DELETE = 1;
    const ACCESS_WRITE = 2;
    const ACCESS_READ = 4;
    
    
    
    /**
     * @var integer
     */
    protected $currentTextId = 0;
    
    /**
     * @var integer
     */
    protected $userId = 1;
    
    /**
     * @var integer
     */
    protected $groupId = 0;
    
    /**
     * @var string
     */
    protected $title = '';
    
    /**
     * @var string
     */
    protected $name = '';
    
    /**
     * @var string
     */
    protected $keywords = '';
    
    /**
     * @var string
     */
    protected $css = '';
    
    /**
     * @var string
     */
    protected $javascript = '';
    
    /**
     * @var integer
     */
    protected $hits = 0;
    
    /**
     * @var float
     */
    protected $size = 0.0;
    
    /**
     * @var float
     */
    protected $score = 0.0;
    
    /**
     * @var string
     */
    protected $permissions = '764';
    
    /**
     * @var boolean
     */
    protected $enableComment = false;
    
    /**
     * @var Tk_Type_Date
     */
    protected $modified = null;
    
    /**
     * @var Tk_Type_Date
     */
    protected $created = null;
    
    /**
     * @var Wik_Util_PageLock
     */
    private $lock = null;
    
    
    
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
     * Delete the page and all associated records
     * Must have valid permissions to delete the page.
     *
     * @return integer
     */
    function delete()
    {
        $user = Auth::getUser();
        if (!$user || !$this->canDelete($user)) {
            return 0;
        }
        $i = Wik_Db_TextLoader::deleteByPageId($this->getId());
        $i += Wik_Db_PageLoader::deleteLinkByPageId($this->getId());
        $i += parent::delete();
        return $i;
    }
    
    
    
    
    /**
     * Get the pageLock object
     *
     * @return Wik_Util_PageLock
     */
    function getLock()
    {
        if ($this->lock == null) {
            if ($this->getId() == null) {
                return;
            }
            $this->lock = new Wik_Util_PageLock($this->getId());
        }
        return $this->lock;
    }
    
    
    /**
     * Is this page an orphan, does it have any links in other pages?
     *
     * @return true if page is an orpaned page.
     */
    function isOrphan()
    {
        return Wik_Db_PageLoader::isOrphan($this->getId());
    }
    
    /**
     * Does this user have access to write to the page?
     *
     * @param Wik_Db_User $user
     * @return integer 1 = delete, 2 = write, 4  = read, 0 = no access
     */
    function getUserPermissions($user = null)
    {
        if ($user === null) {
            return (int)$this->permissions[2];
        }
        if ($user->getGroupId() == Wik_Db_User::GROUP_ADMIN) {
            return self::ACCESS_ALL;
        }
        if ($user->getId() == $this->getUserId()) {
            return (int)$this->permissions[0];
        }
        if ($user->getGroupId() == $this->getGroupId()) {
            return (int)$this->permissions[1];
        }
        return (int)$this->permissions[2];
    }
    
    
    /**
     * Does the user have permission to delete this article
     *
     * @param Wik_Db_User $user
     * @return boolean
     */
    function canDelete($user)
    {
        if (!$user) {
            return false;
        }
        return (($this->getUserPermissions($user) & self::ACCESS_DELETE) == self::ACCESS_DELETE);
    }
    
    /**
     * Does the user have permission to write to this article
     *
     * @param Wik_Db_User $user
     * @return boolean
     */
    function canWrite($user)
    {
        if (!$user) {
            return false;
        }
        return (($this->getUserPermissions($user) & self::ACCESS_WRITE) == self::ACCESS_WRITE);
    }
    
    /**
     * Does the user have permission to read this article
     *
     * @param Wik_Db_User $user
     * @return boolean
     */
    function canRead($user)
    {
        return (($this->getUserPermissions($user) & self::ACCESS_READ) ==  self::ACCESS_READ);
    }
    
    
    
    
    /**
     * Get the page Url
     *
     * @return Tk_Type_Url
     */
    function getPageUrl()
    {
        return new Tk_Type_Url('/page/' . $this->getName());
    }
    
    /**
     * The current content record to associate this page with
     * Safe
     *  Range: A normal-size integer. The signed range is -2147483648 to
     *  2147483647. The unsigned range is 0 to 4294967295.
     *
     * @return integer
     */
    function getCurrentTextId()
    {
        return $this->currentTextId;
    }
    
    /**
     * The current content record to associate this page with
     * Safe
     *  Range: A normal-size integer. The signed range is -2147483648 to
     *  2147483647. The unsigned range is 0 to 4294967295.
     *
     * @param integer $i
     */
    function setCurrentTextId($i)
    {
        $this->currentTextId = $i;
    }
    
    /**
     * The user who created the page
     * Safe
     *  Range: A normal-size integer. The signed range is -2147483648 to
     *  2147483647. The unsigned range is 0 to 4294967295.
     *
     * @return integer
     */
    function getUserId()
    {
        return $this->userId;
    }
    
    /**
     * The user who created the page
     * Safe
     *  Range: A normal-size integer. The signed range is -2147483648 to
     *  2147483647. The unsigned range is 0 to 4294967295.
     *
     * @param integer $i
     */
    function setUserId($i)
    {
        $this->userId = $i;
    }
    
    /**
     * The group who created the page
     * Safe
     *  Range: A normal-size integer. The signed range is -2147483648 to
     *  2147483647. The unsigned range is 0 to 4294967295.
     *
     * @return integer
     */
    function getGroupId()
    {
        return $this->groupId;
    }
    
    /**
     * The group who created the page
     * Safe
     *  Range: A normal-size integer. The signed range is -2147483648 to
     *  2147483647. The unsigned range is 0 to 4294967295.
     *
     * @param integer $i
     */
    function setGroupId($i)
    {
        $this->groupId = $i;
    }
    
    /**
     * Get title
     * Range: A string with 255 characters.
     *
     * @return string
     */
    function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set title
     * Range: A string with 255 characters.
     *
     * @param string $value
     */
    function setTitle($value)
    {
        $this->title = $value;
    }
    
    /**
     * Get name, this is the name to use for urls
     * Range: A string with 255 characters.
     * Valid characters are: 'a-zA-Z0-9_-'
     *
     *
     * @return string
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     * Set page name, this is the name to use for urls
     * Range: A string with 255 characters.
     * Valid characters are: 'a-zA-Z0-9_-'
     *
     * @param string $value
     */
    function setName($value)
    {
        $this->name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $value);
    }
    
    /**
     * User defined meta keywrds for the internal search engine
     * Range:
     *  A string with 255 characters.
     *
     * @return string
     */
    function getKeywords()
    {
        return $this->keywords;
    }
    
    /**
     * User defined meta keywrds for the internal search engine
     * Range:
     *  A string with 255 characters.
     *
     * @param string $value
     */
    function setKeywords($value)
    {
        $this->keywords = $value;
    }
    
    /**
     * Any user defined css inline styles
     * Safe Range: A string with a
     *  maximum length of 65,535 characters.
     *
     * @return string
     */
    function getCss()
    {
        return $this->css;
    }
    
    /**
     * Any user defined css inline styles
     * Safe Range: A string with a
     *  maximum length of 65,535 characters.
     *
     * @param string $value
     */
    function setCss($value)
    {
        $this->css = $value;
    }
    
    /**
     * Any user defined inline javascript
     * Safe Range: A string with a
     *  maximum length of 65,535 characters.
     *
     * @return string
     */
    function getJavascript()
    {
        return $this->javascript;
    }
    
    /**
     * Any user defined inline javascript
     * Safe Range: A string with a
     *  maximum length of 65,535 characters.
     *
     * @param string $value
     */
    function setJavascript($value)
    {
        $this->javascript = $value;
    }
    
    /**
     * The page views per session
     * Safe Range: A normal-size integer.
     *  The signed range is -2147483648 to 2147483647. The unsigned range is 0
     *  to 4294967295.
     *
     * @return integer
     */
    function getHits()
    {
        return $this->hits;
    }
    
    /**
     * The page views per session
     * Safe Range: A normal-size integer.
     *  The signed range is -2147483648 to 2147483647. The unsigned range is 0
     *  to 4294967295.
     *
     * @param integer $i
     */
    function setHits($i)
    {
        $this->hits = $i;
    }
    
    /**
     * The page content size in bytes
     * Safe Range: A normal-size
     *  integer. The signed range is -2147483648 to 2147483647. The unsigned
     *  range is 0 to 4294967295.
     *
     * @return float
     */
    function getSize()
    {
        return $this->size;
    }
    
    /**
     * The page content size in bytes
     * Safe Range: A normal-size
     *  integer. The signed range is -2147483648 to 2147483647. The unsigned
     *  range is 0 to 4294967295.
     *
     * @param float $i
     */
    function setSize($i)
    {
        $this->size = $i;
    }
    
    /**
     * The page content size in bytes
     * Safe Range: A normal-size
     *  integer. The signed range is -2147483648 to 2147483647. The unsigned
     *  range is 0 to 4294967295.
     *
     * @return float
     */
    function getScore()
    {
        return $this->score;
    }
    
    /**
     * Enter description here...
     *
     * @return unknown
     */
    function getPermisssionsString()
    {
        $permStr = array();
        $permArray = array($this->permissions[0], $this->permissions[1], $this->permissions[2]);
        foreach ($permArray as $p) {
            $p = (int)$p;
            $str = '';
            if (($p & self::ACCESS_READ) == self::ACCESS_READ) {
                $str .= 'r';
            } else {
                $str .= "-";
            }
            if (($p & self::ACCESS_WRITE) == self::ACCESS_WRITE) {
                $str .= 'w';
            } else {
                $str .= "-";
            }
            if (($p & self::ACCESS_DELETE) == self::ACCESS_DELETE) {
                $str .= 'd';
            } else {
                $str .= "-";
            }
            $permStr[] = $str;
        }
        return $permStr[0] . $permStr[1] . $permStr[2];
    }
    
    /**
     * The wiki page access permissions for users
     * Use bit permissions
     *
     *         owner   group  other
     * read      4      4      4
     * write     2      2      2
     * delete    1      1      1
     *
     * System is based of Linux permission system.
     * So 764 would allow full access to the author, read/write access to groups and read access to users.
     *
     * NOTES:
     *   o Admin users override all access permissions and can read/write/delete all pages.
     *   o Only users and admins can change a pages permissions.
     *
     * @return integer
     */
    function getPermissions()
    {
        return $this->permissions;
    }
    
    /**
     * The wiki page access permissions for users
     * Use bit permissions
     *
     *         owner   group  other
     * read      4      4      4
     * write     2      2      2
     * delete    1      1      1
     *
     * So 764 would allow full access to the author, read/write access to groups and read access to users
     *
     * NOTES:
     *   o Admin users override all access permissions and can read/write/delete all pages.
     *   o Only users and admins can change a pages permissions.
     *
     * @param integer $i
     */
    function setPermissions($i)
    {
        $this->permissions = $i;
    }
    
    /**
     *
     *
     * @return boolean
     */
    function getEnableComment()
    {
        return $this->enableComment;
    }
    
    /**
     *
     *
     * @param boolean $b
     */
    function setEnableComment($b)
    {
        $this->enableComment = $b;
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
 * A validator object for the Wik_Db_Page object
 *
 * @package Util
 */
class Wik_Db_PageValidator extends Tk_Util_Validator
{
    
    /**
     * @var Wik_Db_Page
     */
    protected $obj = null;
    
    /**
     * Validates
     *
     */
    function validate()
    {
        
        if (!preg_match('/^.{1,255}$/', $this->obj->getName())) {
            $this->setError('title', 'Invalid Page Name Value.');
        }
        if (!preg_match('/^.{1,255}$/', $this->obj->getTitle())) {
            $this->setError('title', 'Invalid Page Title Value.');
        }
        if (!preg_match('/^.{0,255}$/', $this->obj->getKeywords())) {
            $this->setError('keywords', 'Invalid Keyword Value.');
        }
        if (!preg_match('/^[0-7]{3}$/', $this->obj->getPermissions())) {
            $this->setError('permissions', 'Invalid Permissions Value (eg. 764).');
        }
        
        
    }

}
