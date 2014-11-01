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
class Wik_Db_SettingsLoader extends Tk_Object
{
    
    /**
     * Load the data map
     *
     */
    function getDataMap()
    {
        $dataMap = new Tk_Loader_DataMap(__CLASS__);

        $dataMap->addIdProperty('id', Tk_Object::T_INTEGER);
        $dataMap->addProperty('title', Tk_Object::T_STRING);
        $dataMap->addProperty('siteEmail', Tk_Object::T_STRING);
        $dataMap->addProperty('contact', Tk_Object::T_STRING);
        $dataMap->addProperty('metaDescription', Tk_Object::T_STRING);
        $dataMap->addProperty('metaKeywords', Tk_Object::T_STRING);
        $dataMap->addProperty('footerScript', Tk_Object::T_STRING);
        $dataMap->addProperty('gmapKey', Tk_Object::T_STRING);
        $dataMap->addProperty('modified', 'Tk_Type_Date');
        $dataMap->addProperty('created', 'Tk_Type_Date');
        
        return $dataMap;
    }

    // ------- Add custom methods below. -------
    
    /**
     * Find an object by its id
     *
     * @param integer $id
     * @return Wik_Db_Settings
     */
    static function find()
    {
        $loader = Tk_Loader_Factory::getLoader(__CLASS__);
        return Tk_Db_Factory::getDbMapper($loader->getDataMap())->select(1);
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
    
    
}