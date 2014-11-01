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
class Wik_Db_UserLoader extends Tk_Object implements Tk_Loader_Interface
{
    
    /**
     * Load the data map
     *
     */
    function getDataMap()
    {
        $dataMap = new Tk_Loader_DataMap(__CLASS__);
        
        $dataMap->addIdProperty('id', Tk_Object::T_INTEGER);
        $dataMap->addProperty('name', Tk_Object::T_STRING);
        $dataMap->addProperty('email', Tk_Object::T_STRING);
        $dataMap->addProperty('image', Tk_Object::T_STRING);
        $dataMap->addProperty('active', Tk_Object::T_BOOLEAN);
        $dataMap->addProperty('username', Tk_Object::T_STRING);
        $dataMap->addProperty('password', Tk_Object::T_STRING);
        $dataMap->addProperty('groupId', Tk_Object::T_INTEGER);
        $dataMap->addProperty('hash', Tk_Object::T_STRING);
        $dataMap->addProperty('modified', 'Tk_Type_Date');
        $dataMap->addProperty('created', 'Tk_Type_Date');
        
        return $dataMap;
    }
    
    // ------- Add custom query methods below. -------
    

    /**
     * Find by its id
     * 
     * @param integer $id
     * @return Wik_Db_User
     */
    static function find($id)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->select($id);
    }
    
    /**
     * Find all
     * 
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findAll($tool = null)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany('', $tool);
    }
    
    /**
     * Find all active users only
     * 
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findActive($tool = null)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany('`active` = 1', $tool);
    }
    
    /**
     * Find by username
     * 
     * @param string $username
     * @return Wik_Db_User
     */
    static function findByUsername($username)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $username = Tk_Db_MyDao::escapeString($username);
        $arr = Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany('`username` = \'' . $username . '\' AND `active` = 1');
        return $arr->current();
    }
    
    /**
     * Find by hash
     * 
     * @param string $hash
     * @return Wik_Db_User
     */
    static function findByHash($hash)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $hash = Tk_Db_MyDao::escapeString($hash);
        $arr = Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany('`hash` = \'' . $hash . '\' AND `active` = 0');
        return $arr->current();
    }
    
    /**
     * Find filtered record for Manager
     *
     * @param array $filter
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findFiltered($filter = array(), $tool = null)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        
        $where = '';
        if (isset($filters['keywords'])) {
            $kw = '%'.Tk_Db_MyDao::escapeString($filters['keywords']).'%';
            $w = '';
            //$w .= sprintf('`name` LIKE %s OR', enquote($kw));
            if (is_numeric($filters['keywords'])) {
                $id = intval($filters['keywords']);
                $w .= sprintf("`id` = %d OR ", $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }
        if(!empty($filter['groupId'])) {
            $where = sprintf('`groupId` = %s AND ', (int)$filter['group']);
        }
        if ($where != null) {
            $where = substr($where, 0, -4);
        }
        
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany($where, $tool);
    }
    
    /**
     * Find by email
     * 
     * @param Tk_Db_Tool $tool
     * @return Wik_Db_User
     */
    static function findByEmail($email)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $email = Tk_Db_MyDao::escapeString($email);
        $arr = Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany('`email` = \'' . $email . '\'');
        return $arr->current();
    }
    
    /**
     * Find by email
     * 
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findByGroup($group = 0, $tool = null)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $group = intval($group);
        $where = '';
        if ($group > 0) {
            $where = sprintf('`groupId` = %d', $group);
        }
        $arr = Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany($where, $tool);
        return $arr;
    }
    
    /**
     * Find all users who contributed to the article but not the author
     * 
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findContributers($pageId, $tool = null)
    {
        $pageId = (int)$pageId;
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        if ($tool == null) {
            $tool = new Tk_Db_Tool(0, 0, `name`);
        }
        

        $from = sprintf('`user` u, `text` t');
        $where = sprintf('u.`id` = t.`userId` AND t.`pageId` = %d', $pageId);
        
        $arr = Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectFrom($from, $where, $tool, 'u', true);
        return $arr;
    }
    
    /**
     * Clean non-confirmed account after 1 week
     * 
     * @return integer
     */
    static function cleanNonConfirmed()
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $cutoff = Tk_Type_Date::createDate()->addDays(-7);
        $query = sprintf('DELETE FROM `%s` WHERE `hash` != \'\' AND `active` = 0 AND `created` <= \'%s\'', Tk_Db_Factory::getDbMapper($loader->getDataMap())->getTable(), $cutoff->getIsoDate());
        Tk_Db_Factory::getDbMapper($loader->getDataMap())->getDb()->query($query);
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->getDb()->getAffectedRows();
    }

}