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
class Wik_Db_TextLoader extends Tk_Object implements Tk_Loader_Interface
{
    
    /**
     * Load the data map
     *
     */
    function getDataMap()
    {
        $dataMap = new Tk_Loader_DataMap(__CLASS__);
        
        $dataMap->addIdProperty('id', Tk_Object::T_INTEGER);
        $dataMap->addProperty('pageId', Tk_Object::T_INTEGER);
        $dataMap->addProperty('userId', Tk_Object::T_INTEGER);
        $dataMap->addProperty('text', Tk_Object::T_STRING);
        $dataMap->addProperty('created', 'Tk_Type_Date');
        
        return $dataMap;
    }
    
    // ------- Add custom query methods below. -------
    

    /**
     * Find by its id
     * 
     * @param integer $id
     * @return Wik_Obj_Comment
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
     * Find a records by pageId
     *
     * @param string $title
     * @return Tk_Loader_Collection
     */
    static function findByPageId($pageId, $tool = null)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $pageId = intval($pageId);
        $where = sprintf("`pageId` = '%d'", $pageId);
        $arr = Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany($where, $tool);
        return $arr;
    }
    
    /**
     * Delete all text row for a page
     * 
     * @param integer $pageId
     * @return integer
     */
    static function deleteByPageId($pageId)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $pageId = intval($pageId);
        $where = "`pageId` = $pageId";
        $query = sprintf('DELETE FROM `%s` WHERE %s', Tk_Db_Factory::getDbMapper($loader->getDataMap())->getTable(), $where);
        Tk_Db_Factory::getDbMapper($loader->getDataMap())->getDb()->query($query);
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->getDb()->getAffectedRows();
    }

}