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
class Wik_Db_Text extends Tk_Db_Object implements Com_Xml_RssInterface
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
    protected $text = '';
    
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
        $this->created = Tk_Type_Date::createDate();
    
    }
    
    /**
     * This object does not update
     *
     * @param Wik_Db_Text $obj
     * @return integer
     */
    function insert()
    {
        $id = parent::insert();
        $template = Dom_Template::load('<div></div>');
        $doc = $template->getDocument(false);
        Dom_Template::insertHtmlDom($doc->documentElement, $this->getText());
        
        Wik_Db_PageLoader::deleteLinkByPageId($this->getPageId());
        $nodeList = $doc->getElementsByTagName('a');
        foreach ($nodeList as $node) {
            $regs = array();
            if (preg_match('/^page:\/\/(.+)/i', $node->getAttribute('href'), $regs)) {
                if (isset ($regs[1])) {
                    Wik_Db_PageLoader::insertPageLink($this->getPageId(), $regs[1]);
                }
            }
        }
        
        return $id;
    }
    
    /**
     * This object does not have an update
     *
     * @param Wik_Db_Text $obj
     */
    function update()
    {
        return false;
    }
    
    /**
     * The current content record to associate this page with
     * Safe
     *  Range: A normal-size integer. The signed range is -2147483648 to
     *  2147483647. The unsigned range is 0 to 4294967295.
     *
     * @return integer
     */
    function getPageId()
    {
        return $this->pageId;
    }
    
    /**
     * The current content record to associate this page with
     * Safe
     *  Range: A normal-size integer. The signed range is -2147483648 to
     *  2147483647. The unsigned range is 0 to 4294967295.
     *
     * @param integer $i
     */
    function setPageId($i)
    {
        $this->pageId = $i;
    }
    
    /**
     * The user who edited the page
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
     * The user who edited the page
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
     * Get text
     * Safe Range: A string with a maximum length of 65,535
     *  characters.
     *
     * @return string
     */
    function getText()
    {
        return $this->text;
    }
    
    /**
     * Set text
     * Safe Range: A string with a maximum length of 65,535
     *  characters.
     *
     * @param string $value
     */
    function setText($value)
    {
        $this->text = $value;
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
    
    
    /**
     * Get the size in bytes of the text data
     *
     * @return integer
     */
    function getSize()
    {
        return str2Bytes($this->getText());
    }
    
    /**
     * Get the Title string
     *
     * @return string
     */
    function getRssTitle()
    {
        $page = Wik_Db_PageLoader::find($this->getPageId());
        return $page->getTitle();
    }
    
    /**
     * Get the Description string
     *
     * @return string
     */
    function getRssDescr()
    {
        return substr(Wik_Util_TextFormatter::create($this)->getDomDocument()->saveXML(), 0, 500);
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

}

/**
 * A validator object for the Wik_Db_Text object
 *
 * @package Util
 */
class Wik_Db_TextValidator extends Tk_Util_Validator
{
    
    /**
     * @var Wik_Db_Text
     */
    protected $obj = null;
    
    /**
     * Validates
     *
     */
    function validate()
    {
        if ($this->obj->getPageId() > 0) {
            $this->setError('pageId', 'Invalid Page ID.');
        }
    }

}