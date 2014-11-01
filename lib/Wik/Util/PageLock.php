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
class Wik_Util_PageLock extends Tk_Object
{
    
    private $pageId = 0;
    private $userIp = '';
    
    private $timeout = 120;
    
    /**
     * __construct
     *
     */
    function __construct($pageId)
    {
        // TODO: We should be using the page name as the lock not the page Id
        $this->pageId = $pageId;
        $this->userIp = Tk_Request::getInstance()->getRemoteAddr();
    }
    
    /**
     * Enter description here...
     * 
     */
    function lock($userId)
    {
        if ($this->isLocked()) {
            if ($this->hasAccess($userId)) {
                $sql = sprintf('UPDATE `pageLock` SET `expire` = \'%s\' WHERE `hash` = \'%s\'', Tk_Type_Date::createDate()->addSeconds($this->timeout)->getIsoDate(), md5($this->pageId . $userId . $this->userIp));
                Tk_Db_Factory::getDb()->query($sql);
            }
        } else {
            $sql = sprintf('INSERT INTO `pageLock` VALUES (\'%s\', %d, %d, \'%s\', \'%s\')', md5($this->pageId . $userId . $this->userIp), $this->pageId, $userId, $this->userIp, Tk_Type_Date::createDate()->addSeconds($this->timeout)->getIsoDate());
            Tk_Db_Factory::getDb()->query($sql);
        }
    }
    
    /**
     * Enter description here...
     * 
     */
    function unlock($userId)
    {
        if (!$this->isLocked()) {
            return;
        }
        $sql = sprintf('DELETE FROM `pageLock` WHERE `hash` = \'%s\'', md5($this->pageId . $userId . $this->userIp));
        Tk_Db_Factory::getDb()->query($sql);
    }
    
    /**
     * Enter description here...
     * 
     * @return boolean
     */
    function isLocked()
    {
        self::clearExpired();
        $sql = sprintf('SELECT COUNT(*) as i FROM `pageLock` WHERE `pageId` = %d', $this->pageId);
        $result = Tk_Db_Factory::getDb()->query($sql);
        $row = $result->current();
        return ($row['i'] > 0);
    }
    
    /**
     * Does the userId have access to the lock
     *
     * @param integer $userId
     * @return boolean
     */
    function hasAccess($userId)
    {
        $sql = sprintf('SELECT COUNT(*) as i FROM `pageLock` WHERE `hash` = \'%s\'', md5($this->pageId . $userId . $this->userIp));
        $result = Tk_Db_Factory::getDb()->query($sql);
        $row = $result->current();
        return ($row['i'] > 0);
    }
    
    /**
     * Can a user edit a page
     *
     * @param integer $userId
     * @return boolean
     */
    function isEditable($userId)
    {
        if (!$this->isLocked()) {
            return true;
        }
        if ($this->hasAccess($userId)) {
            return true;
        }
        return false;
    }
    
    /**
     * Enter description here...
     * 
     */
    static function clearExpired()
    {
        $sql = sprintf('DELETE FROM `pageLock` WHERE `expire` < \'%s\'', Tk_Type_Date::createDate()->getIsoDate());
        Tk_Db_Factory::getDb()->query($sql);
    }
}