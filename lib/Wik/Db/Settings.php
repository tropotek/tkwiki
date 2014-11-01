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
class Wik_Db_Settings extends Tk_Db_Object
{
    static private $instance = null;
    
    
    /**
     * @var string
     */
    protected $title = '';
    
    /**
     * @var string
     */
    protected $siteEmail = '';
    
    /**
     * @var string
     */
    protected $contact = '';
    
    /**
     * @var string
     */
    protected $metaDescription = '';
    
    /**
     * @var string
     */
    protected $metaKeywords = '';
    
    /**
     * @var string
     */
    protected $footerScript = '';
    
    /**
     * @var string
     */
    protected $gmapKey = '';
    
    
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
     * getInstance
     *
     * @return Wik_Db_Settings
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = Wik_Db_SettingsLoader::find();
        }
        return self::$instance;
    }
    
    

    
    /**
     * Optional: Used for emails and dynamic TEXT that requires a site name
     * Range: A string with 255 characters.
     *
     * @return string
     */
    function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Optional: Used for emails and dynamic TEXT that requires a site name
     * Range: A string with 255 characters.
     *
     * @param string $value
     */
    function setTitle($value)
    {
        $this->title = $value;
    }
    
    /**
     * Optional: Used on emails and dynamic TEXT that requires a site slogan
     * Range: A string with 255 characters.
     *
     * @return string
     */
    function getSiteEmail()
    {
        return $this->siteEmail;
    }
    
    /**
     * Optional: Used on emails and dynamic TEXT that requires a site slogan
     * Range: A string with 255 characters.
     *
     * @param string $value
     */
    function setSiteEmail($value)
    {
        $this->siteEmail = $value;
    }
    
    /**
     * Optional: Used on emails and dynamic TEXT that requires a site slogan
     * Range: A string with 255 characters.
     *
     * @return string
     */
    function getContact()
    {
        return $this->contact;
    }
    
    /**
     * Optional: Used on emails and dynamic TEXT that requires a site slogan
     * Range: A string with 255 characters.
     *
     * @param string $value
     */
    function setContact($value)
    {
        $this->contact = $value;
    }
    
    /**
     * Optional: Used for meta data description
     * Safe Range: A string
     *  with a maximum length of 65,535 characters.
     *
     * @return string
     */
    function getMetaDescription()
    {
        return $this->metaDescription;
    }
    
    /**
     * Optional: Used for meta data description
     * Safe Range: A string
     *  with a maximum length of 65,535 characters.
     *
     * @param string $value
     */
    function setMetaDescription($value)
    {
        $this->metaDescription = $value;
    }
    
    /**
     * Optional: Used for meta data keywords
     * Safe Range: A string with
     *  a maximum length of 65,535 characters.
     *
     * @return string
     */
    function getMetaKeywords()
    {
        return $this->metaKeywords;
    }
    
    /**
     * Optional: Used for meta data keywords
     * Safe Range: A string with
     *  a maximum length of 65,535 characters.
     *
     * @param string $value
     */
    function setMetaKeywords($value)
    {
        $this->metaKeywords = $value;
    }
    
    /**
     * Optional: Javascript for footer of all public pages
     * Safe Range:
     *  A string with a maximum length of 65,535 characters.
     *
     * @return string
     */
    function getFooterScript()
    {
        return $this->footerScript;
    }
    
    /**
     * Optional: Javascript for footer of all public pages
     * Safe Range:
     *  A string with a maximum length of 65,535 characters.
     *
     * @param string $value
     */
    function setFooterScript($value)
    {
        $this->footerScript = $value;
    }
    
    /**
     * Optional: Javascript for footer of all public pages
     * Safe Range:
     *  A string with a maximum length of 65,535 characters.
     *
     * @return string
     */
    function getGmapKey()
    {
        return $this->gmapKey;
    }
    
    /**
     * Optional: Javascript for footer of all public pages
     * Safe Range:
     *  A string with a maximum length of 65,535 characters.
     *
     * @param string $value
     */
    function setGmapKey($value)
    {
        $this->gmapKey = $value;
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
 * A validator object for `Wik_Db_Settings`
 *
 * @package Db
 */
class Wik_Db_SettingsValidator extends Tk_Util_Validator
{

    /**
     * @var Wik_Db_Settings
     */
    protected $obj = null;

    /**
     * Validates
     *
     */
    function validate()
    {
        
        if (!preg_match('/^.{1,255}$/', $this->obj->getTitle())) {
            $this->setError('title', 'Invalid Title Value.');
        }
        if (!preg_match(self::REG_EMAIL, $this->obj->getSiteEmail())) {
            $this->setError('siteEmail', 'Invalid Site Email Value.');
        }
    }

}