<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A basic example of an object factory.
 *
 * <code>
 * <?php
 *    Tk_Db_ObjectFactory::getInstance()->getDb();
 * ?>
 * </code>
 *
 * @package Tk
 */
class Tk_Db_Factory extends Tk_Object
{
    
    /**
     * @var Tk_Db_ObjectFactory
     */
    protected static $instance = null;
    
    
    /**
     * @var Tk_Db_MyDao
     */
    protected $myDb = null;
    /**
     * @var Tk_Db_MsDao
     */
    protected $msDb = null;
    /**
     * @var Tk_Db_OdbcDao
     */
    protected $odbcDb = null;
    
    
    public $loadCount = 0;
    
    
    /**
     * This is a constructor
     * If no request session or response parameters given the default Tk objects are used.
     *
     */
    protected function __construct()
    {
    }
    
    /**
     * Get an instance of the object factory
     *
     * @return Tk_Db_ObjectFactory
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    static function getLoadCount()
    {
        return self::getInstance()->loadCount;
    }
    
    /**
     * Get the mapper using the supplied data map/loader
     * If the loader is supplied then the getDataMap() is called for the data map
     *
     * @param Tk_Loader_DataMap $dataMap Can also take a Tk_Loader_Interface
     * @return Tk_Db_Mapper
     */
    static function getDbMapper($dataMap)
    {
        if ($dataMap instanceof Tk_Db_Map_DataMap) {
            return Tk_Db_Map_Mapper::getInstance($dataMap);
        } else {
            return Tk_Db_Mapper::getInstance($dataMap);
        }
    }
    
    /**
     *
     * @param type $class
     * @return Tk_Loader_DataMap 
     */
    static function makeDataMap($class, $dataSrc = '')
    {
        if (Tk_Config::get('database.newMapper')) {
            return new Tk_Db_Map_DataMap($class, $dataSrc);
        } else {
            return new Tk_Loader_DataMap($class, $dataSrc);
        }
    }
    
    
    
    /**
     * Alias to ::getMyDb()
     *
     * @return Tk_Db_MyDao
     */
    static function getDb($configGroup = 'default')
    {
        $cfg = Tk_Config::get('database.'.$configGroup);
        if (!$cfg) {
            throw new Tk_Exception('Database config not found!');
        }
        if (!isset($cfg['type'])) {
            $cfg['type'] = 'mysql';
        }
        switch (strtolower($cfg['type'])) {
            case 'odbc':
                return self::getOdbcDb($cfg);
            case 'mssql':
                return self::getMsDb($cfg);
            case 'postgress':
            default:
                return self::getMyDb($cfg);
        }
    }
    
    /**
     * Get a database object
     *
     * @return Tk_Db_MyDao
     */
    static function getMyDb($cfg)
    {
        return self::getInstance()->makeDb($cfg);
    }
    
    /**
     * Make a db object
     *
     * @return Tk_Db_MyDao
     */
    private function makeDb($cfg)
    {
        if ($this->myDb == null) {
            $this->myDb = new Tk_Db_MyDao($cfg['user'], $cfg['password'], $cfg['host']);
            $this->myDb->selectDb($cfg['name']);
        }
        return $this->myDb;
    }
    
    
    
    
    /**
     * Get a database object
     *
     * @return Ext_Db_OdbcDao
     */
    static function getOdbcDb($cfg)
    {
        return self::getInstance()->makeOdbcDb($cfg);
    }
    
    /**
     * Make a odbcDb object
     * The host is the odbc resource name
     *
     * @return Ext_Db_OdbcDao
     */
    private function makeOdbcDb($cfg)
    {
        if ($this->odbcDb == null) {
            $this->odbcDb = new Tk_Db_OdbcDao($cfg['user'], $cfg['password'], $cfg['host']);
            $this->odbcDb->selectDb($cfg['name']);
        }
        return $this->odbcDb;
    }
    
    
    
    
    /**
     * Get a database object
     *
     * @return Tk_Db_MsDao
     */
    static function getMsDb($cfg)
    {
        return self::getInstance()->makeMsDb($cfg);
    }
    
    /**
     * Make a mssql object
     * The host is the `server\instance` path
     *
     * @return Tk_Db_MsDao
     */
    private function makeMsDb($cfg)
    {
        if ($this->msDb == null) {
            $this->msDb = new Tk_Db_MsDao($cfg);
            //$this->msDb->selectDb();
        }
        return $this->msDb;
    }
    
    
    
}