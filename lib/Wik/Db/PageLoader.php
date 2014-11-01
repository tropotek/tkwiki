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
class Wik_Db_PageLoader extends Tk_Object implements Tk_Loader_Interface
{
    
    /**
     * Load the data map
     *
     */
    function getDataMap()
    {
        $dataMap = new Tk_Loader_DataMap(__CLASS__);
        
        $dataMap->addIdProperty('id', Tk_Object::T_INTEGER);
        $dataMap->addProperty('currentTextId', Tk_Object::T_INTEGER);
        $dataMap->addProperty('userId', Tk_Object::T_INTEGER);
        $dataMap->addProperty('groupId', Tk_Object::T_INTEGER);
        $dataMap->addProperty('title', Tk_Object::T_STRING);
        $dataMap->addProperty('name', Tk_Object::T_STRING);
        $dataMap->addProperty('keywords', Tk_Object::T_STRING);
        $dataMap->addProperty('css', Tk_Object::T_STRING);
        $dataMap->addProperty('javascript', Tk_Object::T_STRING);
        $dataMap->addProperty('hits', Tk_Object::T_INTEGER);
        $dataMap->addProperty('size', Tk_Object::T_FLOAT);
        $dataMap->addProperty('score', Tk_Object::T_FLOAT);
        $dataMap->addProperty('permissions', Tk_Object::T_STRING);
        $dataMap->addProperty('enableComment', Tk_Object::T_BOOLEAN);
        $dataMap->addProperty('modified', 'Tk_Type_Date');
        $dataMap->addProperty('created', 'Tk_Type_Date');
        
        return $dataMap;
    }
    
    // ------- Add custom query methods below. -------
    

    /**
     * Find by its id
     *
     * @param integer $id
     * @return Wik_Db_Page
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
     * Find an authors pages
     *
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findByUserId($userId, $tool = null)
    {
        $userId = (int)$userId;
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany('`userId` = '.$userId, $tool);
    }
    
    /**
     * Find a page(s) by its non-unique title
     *
     * @param string $title
     * @return Tk_Loader_Collection
     */
    static function findByTitle($title)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $title = Tk_Db_MyDao::escapeString($title);
        $where = sprintf("`title` = '%s'", $title);
        
        $arr = Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany($where, new Tk_Db_Tool(1));
        return $arr;
    }
    
    /**
     * Find a page by its unique Name
     *
     * @param string $title
     * @return Wik_Db_Page Returns null if not found
     */
    static function findByName($title)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $title = Tk_Db_MyDao::escapeString($title);
        $where = sprintf("`name` = '%s'", $title);
        $arr = Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany($where, new Tk_Db_Tool(1));
        return $arr->current();
    }
    
    /**
     * Return all the Orphaned Pages
     *
     * @return Tk_Loader_Collection
     */
    static function findOrphanedPages()
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $query = sprintf('SELECT %s FROM `%s` p LEFT JOIN `pageLink` pl ON (p.`name` = pl.`pageToName`)
WHERE pl.`pageFrom` IS NULL  AND (p.`name` != \'Home\' AND p.`name` != \'Menu\')', $mapper->getSelectList(), $mapper->getTable());
        $result = $mapper->getDb()->query($query);
        $coll = $mapper->makeCollection($result);
        return $coll;
    }
    
    /**
     * Return all the Orphaned Pages
     *
     * @return Tk_Loader_Collection
     */
    static function isOrphan($pageId)
    {
        $pageId = (int)$pageId;
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $query = sprintf('SELECT %s FROM `%s` p LEFT JOIN `pageLink` pl ON (p.`name` = pl.`pageToName`)
WHERE pl.`pageFrom` IS NULL AND (p.`name` != \'Home\' AND p.`name` != \'Menu\') AND p.`id` = %d', $mapper->getSelectList(), $mapper->getTable(), $pageId);
        $result = $mapper->getDb()->query($query);
        if ($result->current()) {
            return true;
        }
        return false;
    }
    
    /**
     * insert a page link record
     *
     * @param integer $pageFromId
     * @param string $pageToName
     * @return boolean
     */
    static function insertPageLink($pageFromId, $pageToName)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $pageFromId = intval($pageFromId);
        $pageToName = $mapper->getDb()->escapeString($pageToName);
        if (self::pageLinkExists($pageFromId, $pageToName)) {
            return false;
        }
        $query = sprintf("INSERT INTO `pageLink` VALUES (%d, '%s')", $pageFromId, $pageToName);
        $mapper->getDb()->query($query);
        return true;
    }
    
    /**
     * Check if a page link already exists
     *
     * @param integer $pageFromId
     * @param string $pageToName
     * @return boolean
     */
    static function pageLinkExists($pageFromId, $pageToName)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $pageFromId = intval($pageFromId);
        $pageToName = $mapper->getDb()->escapeString($pageToName);
        $query = sprintf("SELECT COUNT(*) as i FROM `pageLink` WHERE `pageFrom` = %d AND `pageToName` = '%s'", $pageFromId, $pageToName);
        $result = $mapper->getDb()->query($query);
        $value = $result->current();
        return $value['i'] > 0;
    }
    
    /**
     * delete a specific page link
     *
     * @param integer $pageFromId
     * @param string $pageToName
     * @return integer
     */
    static function deletePageLink($pageFromId, $pageToName)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $pageFromId = intval($pageFromId);
        $pageToName = $mapper->getDb()->escapeString($pageToName);
        if (!self::pageLinkExists($pageFromId, $pageToName)) {
            return 0;
        }
        $where = "`pageFrom` = $pageFromId AND `pageToName` = '$pageToName'";
        $query = sprintf('DELETE FROM `pageLink` WHERE %s LIMIT 1', $where);
        $mapper->getDb()->query($query);
        return $mapper->getDb()->getAffectedRows();
    }
    
    /**
     * Delete all links to and from a pageId
     *
     * @param integer $pageId
     * @return integer
     */
    static function deleteLinkByPageId($pageId)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $pageId = intval($pageId);
        $where = "`pageFrom` = $pageId";
        $query = sprintf('DELETE FROM `pageLink` WHERE %s', $where);
        $mapper->getDb()->query($query);
        return $mapper->getDb()->getAffectedRows();
    }
    
    /**
     * Enter description here...
     *
     * @param string $keywords
     * @param string $orderBy
     * @param integer $limit
     * @param integer $offset
     * @return Tk_Loader_Collection
     */
    static function textSearch($keywords = '', $mode = '', $tool = null)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $mapper = Tk_Db_Factory::getDbMapper($loader->getDataMap());
        $keywords = Tk_Db_MyDao::escapeString($keywords);
        if (!$tool) {
            $tool = new Tk_Db_Tool(0, 0, '`score` DESC');
        }
        $orderBy = '';
        if ($tool->getOrderBy() != '') {
            $orderBy = 'ORDER BY ' . $tool->getOrderBy();
        }
        $limitStr = '';
        if ($tool->getLimit() > 0) {
            $limitStr = sprintf("LIMIT %d , %d", $tool->getOffset(), $tool->getLimit());
        }
        
        $query = sprintf("SELECT %s, (IFNULL(pf.`score`,0) + IFNULL(tf.`score`,0)) AS `score`
FROM `page` p LEFT JOIN (
    SELECT sp1.`id` as `pageId`, sp1.`currentTextId` as `textId`, MATCH (sp1.`title`, sp1.`keywords`) AGAINST ('%s' %s) AS `score`
    FROM `page` sp1
    WHERE MATCH (sp1.`title`, sp1.`keywords`) AGAINST ('%s' %s)
    GROUP BY sp1.`name`
    HAVING `score` > 0.0
) pf ON (p.`id` = pf.`pageId`) LEFT JOIN (
    SELECT t.`pageId`, t.`id` as `textId`, MATCH (t.`text`) AGAINST ('%s' %s) AS `score`
    FROM `text` t, `page` sp
    WHERE MATCH (t.`text`) AGAINST ('%s' %s) AND t.`id` = sp.`currentTextId`
    AND t.`id` = sp.`currentTextId`
    GROUP BY `pageId`
    HAVING `score` > 0.0
) tf ON (p.`id` = tf.`pageId`)
WHERE pf.`pageId` IS NOT NULL OR tf.`pageId` IS NOT NULL AND p.`name` != 'MENU'
GROUP BY p.`id`
HAVING `score` > 0.1

%s %s
", str_replace('p.`score`,', '', $mapper->getSelectList('p')), $keywords, $mode, $keywords, $mode,
            $keywords, $mode, $keywords, $mode,$orderBy, $limitStr);
        
        $result = $mapper->getDb()->query($query);
        return $mapper->makeCollection($result, $tool);
    }

}