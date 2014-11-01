<?php
/*       -- TkLib Auto Class Builder --
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Tropotek Development
 */

/**
 *
 *
 * @package Db
 */
class Wik_Db_Comment extends Tk_Db_Object  implements Com_Ui_SelectObjInterface, Com_Xml_RssInterface
{
    
    
    /**
     * @var integer
     */
    protected $pageId = 0;
    
    /**
     * @var integer
     */
    protected $userId = 0;
    
    /**
     * @var string
     */
    protected $ip = 0;
    
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
    protected $web = '';
    
    /**
     * @var string
     */
    protected $comment = '';
    
    /**
     * @var boolean
     */
    protected $deleted = false;
    
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
     * Get the Title string
     *
     * @return string
     */
    function getRssTitle()
    {
        return $this->getName();
    }
    
    /**
     * Get the Description string
     *
     * @return string
     */
    function getRssDescr()
    {
        return $this->getComment();
    }
    
    /**
     * Get the item view url
     *
     * @return Tk_Type_Url
     */
    function getRssLink()
    {
        $page = Wik_Db_PageLoader::find($this->getPageId());
        $url = new Tk_Type_Url('/page/' . $page->getTitle());
        return $url;
    }


    
    /**
     * Return the option label
     *
     * @return string
     */
    function getSelectText()
    {
        return $this->getName();
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
     * Get pageId
     * Safe Range: A normal-size integer. The signed range
     *  is -2147483648 to 2147483647. The unsigned range is 0 to 4294967295.
     *
     * @return integer
     */
    function getPageId()
    {
        return $this->pageId;
    }
    
    /**
     * Set pageId
     * Safe Range: A normal-size integer. The signed range
     *  is -2147483648 to 2147483647. The unsigned range is 0 to 4294967295.
     *
     * @param integer $i
     */
    function setPageId($i)
    {
        $this->pageId = $i;
    }
    
    /**
     * Get userId
     * Safe Range: A normal-size integer. The signed range
     *  is -2147483648 to 2147483647. The unsigned range is 0 to 4294967295.
     *
     * @return integer
     */
    function getUserId()
    {
        return $this->userId;
    }
    
    /**
     * Set userId
     * Safe Range: A normal-size integer. The signed range
     *  is -2147483648 to 2147483647. The unsigned range is 0 to 4294967295.
     *
     * @param integer $i
     */
    function setUserId($i)
    {
        $this->userId = $i;
    }
    
    /**
     * Get IP
     *
     *
     * @return string
     */
    function getIp()
    {
        return $this->ip;
    }
    
    /**
     * Set IP
     *
     * @param string $ip
     */
    function setIp($ip)
    {
        $this->ip = $ip;
    }
    
    /**
     * Get name
     * Range: A string with 128 characters.
     *
     * @return string
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     * Set name
     * Range: A string with 128 characters.
     *
     * @param string $value
     */
    function setName($value)
    {
        $this->name = $value;
    }
    
    /**
     * Get email
     * Range: A string with 128 characters.
     *
     * @return string
     */
    function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Set email
     * Range: A string with 128 characters.
     *
     * @param string $value
     */
    function setEmail($value)
    {
        $this->email = $value;
    }
    
    /**
     * Get web
     * Range: A string with 255 characters.
     *
     * @return string
     */
    function getWeb()
    {
        return $this->web;
    }
    
    /**
     * Set web
     * Range: A string with 255 characters.
     *
     * @param string $value
     */
    function setWeb($value)
    {
        $this->web = $value;
    }
    
    /**
     * Get comment
     * Safe Range: A string with a maximum length of
     *  65,535 characters.
     *
     * @return string
     */
    function getComment()
    {
        return $this->comment;
    }
    
    /**
     * Set comment
     * Safe Range: A string with a maximum length of
     *  65,535 characters.
     *
     * @param string $value
     */
    function setComment($value)
    {
        $this->comment = $value;
    }
    
    /**
     * Get deleted
     *
     * @return boolean
     */
    function getDeleted()
    {
        return $this->deleted;
    }
    
    /**
     * Set deleted
     *
     * @param boolean $b
     */
    function setDeleted($b)
    {
        $this->deleted = $b;
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
 * A validator object for `Wik_Db_Comment`
 *
 * @package Db
 */
class Wik_Db_CommentValidator extends Tk_Util_Validator
{

    /**
     * @var Wik_Db_Comment
     */
    protected $obj = null;

    /**
     * Validates
     *
     */
    function validate()
    {
        if ($this->obj->getPageId() <= 0) {
            $this->setError('pageId', 'Invalid PageId Value.');
        }
        if (!preg_match('/^.{2,128}$/', $this->obj->getName())) {
            $this->setError('name', 'Invalid Name Value.');
        }
        if (!preg_match(self::REG_EMAIL, $this->obj->getEmail())) {
            $this->setError('email', 'Invalid Email Value.');
        }
//        if ($this->obj->getWeb() && !preg_match('/^http://.+$/', $this->obj->getWeb())) {
//            $this->setError('web', 'Invalid Web Value. Use `http://www.domain.com/` format.');
//        }
        // TODO: check length
//        if (!preg_match('/^.*$/', $this->obj->getComment())) {
//            $this->setError('comment', 'Invalid Comment Value.');
//        }
    }

}