<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The base mapper object that controls the mapping of columns to objects
 *
 * @package Tk
 */
class Tk_Db_Map_Mapper extends Tk_Object implements Tk_Loader_Interface
{
    /**
     * @var Tk_Db_Map_Mapper
     */
    protected static $instance = null;
    
    /**
     * @var Tk_Db_MyDao
     */
    protected $db = null;
    
    /**
     * @var Tk_Db_Map_DataMap
     */
    protected $dataMap = null;
    
    /**
     * __construct
     *
     * @param Tk_Db_MyDao $db
     */
    protected function __construct(Tk_Db_MyDao $db)
    {
        $this->db = $db;
    }
    
    /**
     * Create a mapper with the selected data map,
     * If an object of Tk_Loader_Interface is supplied then the default getDataMap() function is used
     * to obtain the datamap.
     *
     * @param Tk_Db_Map_DataMap $dataMap Can also take a Tk_Loader_Interface type
     * @return Tk_Db_Map_Mapper
     */
    static function getInstance($dataMap)
    {
        if (self::$instance == null) {
            self::$instance = new self(Tk_Db_Factory::getDb());
        }
        // Get the default data map
        if ($dataMap instanceof Tk_Loader_Interface) {
            $dataMap = $dataMap->getDataMap();
        } else if (!$dataMap instanceof Tk_Db_Map_DataMap) {
            throw new Tk_ExceptionIllegalArgument('Invalid datamap object: ' . get_class($dataMap));
        }
        self::$instance->setDataMap($dataMap);
        return self::$instance;
    }
    
    
    /**
     * If using DB prefixes then use this function to prepend the
     * prefix to a table name
     *
     * @param unknown_type $tableName
     * @return unknown
     * @deprecated1
     */
    static function prefix($tableName)
    {
        return $tableName;
    }
    
    /**
     * Make and return a db object
     *
     * @return Tk_Db_MyDao
     */
    function getDb()
    {
        return $this->db;
    }
    
    /**
     * Get the object mapper
     *
     * @return Tk_Db_Map_DataMap
     */
    function getDataMap()
    {
        return $this->dataMap;
    }
    
    /**
     * Set the data map object
     *
     * @param Tk_Db_Map_DataMap $dataMap
     */
    function setDataMap(Tk_Db_Map_DataMap $dataMap)
    {
        $this->dataMap = $dataMap;
    }
    
    /**
     * The class name this mapper is used for.
     *
     * @return string
     */
    function getClass()
    {
        return $this->dataMap->getClass();
    }
    
    /**
     * Create a Tk_Loader_Collection object with the raw data and dbTool object.
     *
     * @param Tk_Db_MyResult $result
     * @param Tk_Db_Tool $tool
     * @return Tk_Db_Array
     */
    function makeCollection(Tk_Db_MyResult $result, $tool = null)
    {
        
        if ($tool == null) {
            $tool = new Tk_Db_Tool();
        }
        
        if ($tool->hasTotal()) {
            $query = $this->db->getLastQuery();
            $total = $this->db->countQuery($query);
            if ($tool->getOffset() > 0 && $total < $tool->getOffset()) { // reset offeset for pager in these cases
                $nQuery = preg_replace('/(LIMIT ([0-9]+)(( )?, ([0-9]+))?)/i', 'LIMIT 0, \5', $query);
                $result = $this->db->query($nQuery);
                $tool->setOffset(0);
            }
            $tool->setTotalRows($total);
        } else {
            $tool->setTotalRows($result->count());
        }
        
        $rows = array();
        foreach ($result as $row) {
            $rows[] = $row;
        }
        $collection = new Tk_Db_Array($this->dataMap, $rows);
        $collection->setDbTool($tool);
        $result->free();
        return $collection;
    }
    
    
    /**
     * loadObject
     *
     * @param array $row
     * @return Tk_Db_Object
     */
    function loadObject($row)
    {
        return $this->getDataMap()->loadObject($row);
//        $class = $this->getDataMap()->getClass();
//        $obj = new $class();
//        /* @var $map Db_Map_Interface */
//        foreach ($this->getDataMap()->getPropertyMaps() as $map) {
//            $value = $map->getPropertyValue($row);
//            $name = $map->getPropertyName();
//            $obj->$name = $value;
//        }
//        return $obj;
    }
    
    /**
     * Get the table from this object's mapper.
     * A prefix for the table name will be added if set in the config.ini 'DbPrefix'
     *
     * @return string
     */
    function getTable()
    {
        return $this->dataMap->getDataSrc();
    }
    
    
    
    
    
    /**
     * Return a select list of fields for a sql query
     *
     * @param string $prepend (optional) Default is a null string
     * @return string
     */
    protected function getSelectList($prepend = '')
    {
        $result = '';
        if ($prepend != null && substr($prepend, -1) != '.') {
            $prepend = $prepend . ".";
        }
        /* @var $map Tk_Db_Map_Interface */
        foreach ($this->getDataMap()->getPropertyMaps() as $map) {
            $nameList = $map->getColumnNames();
            foreach ($nameList as $v) {
                $result .= $prepend . "`" . $v . '`,';
            }
        }
        return substr($result, 0, -1);
    }
    
    /**
     * Return an update list of fields for a sql query
     *
     * @param mixed $obj
     * @return string
     */
    protected function getUpdateList($obj)
    {
        $result = '';
        /* @var $map Tk_Db_Map_Interface */
        foreach ($this->getDataMap()->getPropertyList() as $map) {
            if ($map->getPropertyName() == 'modified' || $map->getPropertyName() == 'modifiedDate') {
                $result .= '`' . current($map->getColumnNames()) . "` = '" . date('Y-m-d H:i:s') . "',";
                continue;
            }
            $valArr = $map->getColumnValue($obj);
            foreach ($valArr as $k => $v) {
                $result .= '`' . $k . "` = " . $v . ",";
            }
        }
        if ($result) {
            $result = substr($result, 0, -1);
        }
        return $result;
    }
    
    /**
     * Get the insert text for a query
     *
     * @param mixed $obj
     * @return string
     */
    protected function getInsertList($obj)
    {
        $columns = '';
        $values = '';
        /* @var $map Tk_Db_Map_Interface */
        foreach ($this->getDataMap()->getPropertyMaps() as $map) {
            $varr = $map->getColumnValue($obj);
            foreach ($varr as $k => $v) {
                $columns .= "`" . $k . '`,';
                $values .= $v . ",";
            }
        }
        return '(' . substr($columns, 0, -1) . ') VALUES(' . substr($values, 0, -1) . ')';
    }
    
    /**
     * Get the string representation of the data
     *
     * @param Tk_Db_Map_Interface $map
     * @param array $row
     */
    private function getSqlColumnValue($map, $row)
    {
        $value = $map->getColumnValue($row);
        if ($value === null) {
            $value = "NULL";
        } else if (is_string($value)) {
            $value = Tk_Db_MyDao::escapeString($value);
            $value = "'$value'";
        }
        return $value;
    }
    
    
    
    /**
     * Select a number of elements from a database
     *
     * @param string $where EG: "`column1` = 4 AND `column2` = string"
     * @param Tk_Db_Tool $tool
     * @param integer $prepend Used for table aliases in a query
     * @param boolean $isDistinct
     * @param string $groupBy
     * @return Tk_Db_Array
     */
    function selectFrom($from = '', $where = '', $tool = null, $prepend = '', $isDistinct = false, $groupBy = '')
    {
        if (!$tool instanceof Tk_Db_Tool) {
            $tool = new Tk_Db_Tool();
        }
        $orderBy = $tool->getOrderBy();
        $prepend = Tk_Db_MyDao::escapeString($prepend);
        $isDistinct = $isDistinct === true ? true : false;
        if ($from == null) {
            $from = sprintf('`%s`', $this->getTable());
        }
        if ($where == null) {
            $where = "1";
        }
        if ($orderBy != '') {
            $orderBy = 'ORDER BY ' . $orderBy;
        }
        $limitStr = '';
        if ($tool->getLimit() > 0) {
            $limitStr = sprintf('LIMIT %d, %d', $tool->getOffset(), $tool->getLimit());
        }
        $distinct = '';
        if ($isDistinct) {
            $distinct = 'DISTINCT';
        }
        $groupBy = '';
        if ($groupBy) {
        	$groupBy = 'GROUP BY ' . $groupBy;
        }
        $query = sprintf('SELECT %s %s FROM %s WHERE %s %s %s %s', $distinct, $this->getSelectList($prepend), $from, $where, $groupBy, $orderBy, $limitStr);
        
        $result = $this->getDb()->query($query);
        return $this->makeCollection($result, $tool);
    }
    
    
    /**
     * Select a number of elements from a database
     *
     * @param string $where EG: "`column1`=4 AND `column2`=string"
     * @param Tk_Db_Tool $tool
     * @return Tk_Db_Array
     */
    function selectMany($where = '', $tool = null)
    {
        return $this->selectFrom('', $where, $tool);
    }
    
    
    /**
     * Select a record from a database
     *
     * @param integer $id
     * @return Tk_Db_Object Returns null on error
     */
    function select($id)
    {
        $this->getDataMap();
        $idFields = $this->getDataMap()->getIdPropertyList();
        $idField = current($idFields);
        if ($idField == null) {
            throw new Tk_ExceptionSql('No Primary Id prperties set in the data mapper.');
        }
        $query = sprintf('SELECT %s FROM `%s` WHERE `%s` = %d LIMIT 1', 
            $this->getSelectList(), $this->getDataMap()->getDataSrc(), 
            current($idField->getColumnNames()), intval($id));
        $result = $this->getDb()->query($query);
        
        if ($result->count()) {
            $obj = $this->loadObject($result->current());
            $result->free();
            return $obj;
        }
    }
    
    
    
    /**
     * Insert this object into the database.
     * Returns the new insert id for this object.
     *
     * @param Tk_Db_Object $obj
     * @return integer
     */
    function insert($obj)
    {
        $query = sprintf('INSERT INTO `%s` %s', $this->getTable(), $this->getInsertList($obj));
        $this->getDb()->query($query);
        $id = $this->getDb()->getInsertID();
        $obj->id = $id;
        if ($this->getDataMap()->getPropertyMap('orderBy')) {
            $this->updateValue($id, 'orderBy', $id);
            $obj->orderBy = $id;
            $this->update($obj);
        }
        return $id;
    }
    
    
    
    /**
     * Update this object in the database.
     * Returns The number of affected rows.
     *
     * @param Tk_Db_Object $obj
     * @return integer The number of affected rows
     */
    function update($obj)
    {
        $where = '';
        /* @var $map Tk_Db_Map_Interface */
        foreach ($this->getDataMap()->getIdPropertyList() as $map) {
            $arr = $map->getColumnValue($obj);
            foreach ($arr as $k => $v) {
                $where .= '`' . $k . '` = ' . $v . ' AND ';
            }
        }
        $where = substr($where, 0, -4);
        $query = sprintf('UPDATE `%s` SET %s WHERE %s', $this->getTable(), $this->getUpdateList($obj), $where);
        $this->getDb()->query($query);
        return $this->getDb()->getAffectedRows();
    }
    
    /**
     * Update a single value in a single row
     *
     * @param integer $id
     * @param string $column
     * @param mixed $value
     * @return integer Return the number of rows affected
     */
//    function updateValue($id, $column, $value)
//    {
//        $where = '';
//        /* @var $map Tk_Db_Map_Interface */
//        foreach ($this->getDataMap()->getIdPropertyList() as $map) {
//            $arr = $map->getColumnValue($obj);
//            foreach ($arr as $k => $v) {
//                $where .= '`' . $k . '` = ' . $v . ' AND ';
//            }
//        }
//        $where = substr($where, 0, -4);
//        $query = sprintf("UPDATE `%s` SET `%s` = '%s' WHERE %s", $this->getTable(), $column, $value, $where);
//        
//        $this->getDb()->query($query);
//        return $this->getDb()->getAffectedRows();
//    }
    
    function updateValue($id, $column, $value)
    {
        $idFields = $this->dataMap->getIdPropertyList();
        $idField = current($idFields);
        $query = sprintf("UPDATE `%s` SET `%s` = '%s' WHERE `%s` = %d", $this->getTable(), $column, $value, $idField->getColumnName(), $id);
        
        $this->db->query($query);
        return $this->db->getAffectedRows();
    }
    
    /**
     * Delete this object from the database.
     * Returns The number of affected rows.
     * 
     * @param Tk_Db_Object $obj
     * @return integer
     */
    function delete($obj)
    {
        $where = '';
        /* @var $map Tk_Db_Map_Interface */
        foreach ($this->getDataMap()->getIdPropertyList() as $map) {
            $arr = $map->getColumnValue($obj);
            foreach ($arr as $k => $v) {
                $where .= '`' . $k . '` = ' . $v . ' AND ';
            }
        }
        $where = substr($where, 0, -4);
        $query = sprintf('DELETE FROM `%s` WHERE %s LIMIT 1', $this->getTable(), $where);
        $this->getDb()->query($query);
        return $this->getDb()->getAffectedRows();
    }
    
    /**
     * Delete an array of Ids from the database
     * 
     * @param array $ids
     * @return integer The number of affected rows.
     */
    function deleteGroup($ids)
    {
        $where = '';
        /* @var $map Tk_Db_Map_Interface */
        foreach ($ids as $id) {
            $where .= '`id` = ' . intval($id) . ' OR ';
        }
        $where = substr($where, 0, -3);
        $query = sprintf('DELETE FROM `%s` WHERE %s LIMIT 1', $this->getTable(), $where);
        $this->getDb()->query($query);
        return $this->getDb()->getAffectedRows();
    }
    
    /**
     * Returns the object id if it is greater than 0 or the nextInsertId if is 0
     *
     * @return integer
     */
    function getVolitileId($obj)
    {
        if ($obj->getId() == 0) {
            $id = $this->getDb()->getNextInsertId($this->getTable());
        } else {
            $id = $obj->getId();
        }
        return $id;
    }
    
    /**
     * Count records in a DB
     *
     * @param string $from
     * @param string $where
     * @param integer $distinctId
     * @return integer
     */
    function count($from = '', $where = '')
    {
        if ($from == '') {
            $from = sprintf("`%s`", $this->getTable());
        }
        if ($where == null) {
            $where = "1";
        }
        $query = sprintf("SELECT COUNT(*) AS i FROM %s WHERE %s", $from, $where);
        
        $result = $this->getDb()->query($query);
        $value = $result->current();
        return intval($value['i'], 10);
    }
    
    /**
     * Find an object by its id
     * 
     * @param integer $id
     * @return Db_Model
     */
    function find($id)
    {
        return $this->select($id);
    }
    
    /**
     * Find all object within the DB tool's parameters
     * 
     * @param Db_Tool $tool
     * @return Db_Array
     */
    function findAll($tool = null)
    {
        return $this->selectMany('', $tool);
    }
    
    
    /**
     * Find first record by created
     * 
     * @return Db_Model
     */
    function findFirst()
    {
        return $this->selectMany('', Tk_Db_Tool::create('`created`', 1))->current(); 
    }
    
    /**
     * Find last object by created
     * 
     * @return Db_Model
     */
    function findLast()
    {
        return $this->selectMany('', Tk_Db_Tool::create('`created` DESC', 1))->current(); 
    }
    
    
    
    
    
    /**
     * Swap the order of 2 records
     *
     * @param Db_Model $fromObj
     * @param Db_Model $toObj
     */
    function orderSwap($fromObj, $toObj)
    {
        if (!$this->getDataMap()->getPropertyMap('orderBy')) {
            return;
        }
        $idFields = $this->getDataMap()->getIdPropertyList();
        $idField = current($idFields);
        $query = sprintf("UPDATE `%s` SET `orderBy` = '%s' WHERE `%s` = %d", $this->getTable(), $toObj->getOrderBy(), $idField->getColumnNames(), $fromObj->getId());
        $this->getDb()->query($query);
        $query = sprintf("UPDATE `%s` SET `orderBy` = '%s' WHERE `%s` = %d", $this->getTable(), $fromObj->getOrderBy(), $idField->getColumnNames(), $toObj->getId());
        $this->getDb()->query($query);
        
        return $this->getDb()->getAffectedRows();
    }
    
    /**
     * Reset the order values to id values.
     *
     */
    function resetOrder()
    {
        if (!$this->getDataMap()->getPropertyMap('orderBy')) {
            return;
        }
        $query = sprintf("UPDATE `%s` SET `orderBy` = `id`", $this->getTable());
        $this->getDb()->query($query);
        return $this->getDb()->getAffectedRows();
    }
}
