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
class Wik_Db_CommentLoader extends Tk_Object
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
        $dataMap->addProperty('ip', Tk_Object::T_STRING);
        $dataMap->addProperty('name', Tk_Object::T_STRING);
        $dataMap->addProperty('email', Tk_Object::T_STRING);
        $dataMap->addProperty('web', Tk_Object::T_STRING);
        $dataMap->addProperty('comment', Tk_Object::T_STRING);
        $dataMap->addProperty('deleted', Tk_Object::T_BOOLEAN);
        $dataMap->addProperty('modified', 'Tk_Type_Date');
        $dataMap->addProperty('created', 'Tk_Type_Date');
        
        return $dataMap;
    }

    // ------- Add custom methods below. -------
    
    /**
     * Find an object by its id
     *
     * @param integer $id
     * @return Wik_Db_Comment
     */
    static function find($id)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->select($id);
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
     * Find all comments for a pageId
     *
     * @param integer $pageId
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findByPageId($pageId, $tool = null)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $pageId = (int)$pageId;
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany('!`deleted` AND `pageId` = ' . $pageId, $tool);
    }

    
    /**
     * Find all comments for a pageId including deleted pages
     *
     * @param integer $pageId
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
     */
    static function findAllByPageId($pageId, $tool = null)
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        $pageId = (int)$pageId;
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->selectMany('`pageId` = ' . $pageId, $tool);
    }
    
}