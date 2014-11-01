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
class Auth_Db_UserLoader extends Tk_Object
{
    
    /**
     * Load the data map
     *
     */
    function getDataMap()
    {
        $dataMap = Tk_Db_Factory::makeDataMap(__CLASS__);

        $dataMap->addIdProperty('id', Tk_Object::T_INTEGER);
        $dataMap->addProperty('username', Tk_Object::T_STRING);
        $dataMap->addProperty('password', Tk_Object::T_STRING);
        $dataMap->addProperty('groupId', Tk_Object::T_INTEGER);
        $dataMap->addProperty('active', Tk_Object::T_BOOLEAN);
        $dataMap->addProperty('hash', Tk_Object::T_STRING);
        $dataMap->addProperty('lastLogin', 'Tk_Type_Date');
        
        $dataMap->addProperty('modified', 'Tk_Type_Date');
        $dataMap->addProperty('created', 'Tk_Type_Date');
        
        return $dataMap;
    }

    // ------- Add custom methods below. -------
    
    /**
     * Find an object by its id
     *
     * @param integer $id
     * @return Auth_Db_User
     */
    static function find($id)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->select($id);
    }

    /**
     * Find User By Username
     *
     * @param string $username
     * @return Auth_Db_User
     */
    static function findByUsername($username, $active = true)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $username = Tk_Db_MyDao::escapeString($username);
        $a = '';
        if ($active) {
            $a = '`active` AND ';
        }
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany($a . '`username` = ' . enquote($username))->current();
    }
    
    /**
     * Find all object within the DB tool's parameters
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
     * Find all object within the DB tool's parameters
     *
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findActive($tool = null)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany('`active`', $tool);
    }
    
    /**
     * findByCategory
     *
     * @param array $filter
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findFiltered($filter = array(), $tool = null)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $where = '';
        
        if (!empty($filter['keywords'])) {
            $keyword = Tk_Db_MyDao::escapeString($filter['keywords']);
            $where .= sprintf('(`username` LIKE \'%%%s%%\') AND ', $keyword, $keyword);
        }
        
        if ($where) {
            $where = substr($where, 0, -4);
        }
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany($where, $tool);
    }
    
    /**
     * Find by hash
     *
     * @param string $hash
     * @return Auth_Db_User
     */
    static function findByHash($hash)
    {
        $hash = Tk_Db_MyDao::escapeString($hash);
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $arr = Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany('`hash` = \'' . $hash . '\'');
        return $arr->current();
    }
    
    /**
     * Find all
     *
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findByGroupId($groupId, $tool = null)
    {
        $groupId = intval($groupId);
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $where = '';
        if ($groupId > 0) {
            $where = '`groupId` = ' . $groupId;
        }
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany($where, $tool);
    }


    /**
     * Delete all inactive users with no activitiy for 6 months
     *
     * @todo Check and implement this in the front controller
     */
    static function cleanUsers()
    {
        $db = Tk_Db_Factory::getDb();
        $now = Tk_Type_Date::createDate()->addMonths(-12);
        $query = sprintf('DELETE FROM `user` WHERE `active` = 0 AND `modified` < %s', enquote($now->ceil()->getIsoDate()));
        $db->query($query);
    }
    
}