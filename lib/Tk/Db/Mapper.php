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
class Tk_Db_Mapper extends Tk_Object
{
    /**
     * @var Tk_Db_Mapper
     */
    protected static $instance = null;
    
    /**
     * @var Tk_Db_MyDao
     */
    protected $db = null;
    
    /**
     * @var Tk_Loader_DataMap
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
     * @param Tk_Loader_DataMap $dataMap Can also take a Tk_Loader_Interface type
     * @return Tk_Db_Mapper
     */
    static function getInstance($dataMap)
    {
        if (self::$instance == null) {
            self::$instance = new self(Tk_Db_Factory::getDb());
        }
        // Get the default data map
        if ($dataMap instanceof Tk_Loader_Interface) {
            $dataMap = $dataMap->getDataMap();
        } else if (!$dataMap instanceof Tk_Loader_DataMap) {
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
     */
    static function prefix($tableName)
    {
//        if (Tk_Config::getInstance()->getDbPrefix()) {
//            return Tk_Config::getInstance()->getDbPrefix() . $tableName;
//        }
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
     * @return Dk_Loader_DataMap
     */
    function getDataMap()
    {
        return $this->dataMap;
    }
    
    /**
     * Set the data map object
     *
     * @param Tk_Loader_DataMap $dataMap
     */
    function setDataMap(Tk_Loader_DataMap $dataMap)
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
     * @return Tk_Loader_Collection
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
        $collection = new Tk_Loader_Collection($this->dataMap, $rows);
        $collection->setDbTool($tool);
        $result->free();
        return $collection;
    }
    
    /**
     * Get the table from this object's mapper.
     * A prefix for the table name will be added if set in the config.ini 'DbPrefix'
     *
     * @return string
     */
    function getTable()
    {
        return self::prefix($this->dataMap->getDataSrc());
    }
    
    /**
     * Return a select list of fields for a sql query
     *
     * @param string $prepend (optional) Default is a null string
     * @return string
     */
    function getSelectList($prepend = '')
    {
        $result = '';
        if ($prepend != null && substr($prepend, -1) != '.') {
            $prepend = $prepend . ".";
        }
        foreach ($this->dataMap->getIdPropertyList() as $map) {
            $result .= $prepend . "`" . $map->getColumnName() . '`,';
        }
        foreach ($this->dataMap->getPropertyList() as $map) {
            $result .= $prepend . "`" . $map->getColumnName() . '`,';
        }
        return substr($result, 0, -1);
    }
    
    /**
     * Return an update list of fields for a sql query
     *
     * @param mixed $obj
     * @return string
     */
    function getUpdateList($obj)
    {
        $row = Tk_Loader_Factory::getInstance()->getObjectValues($obj);
        $result = '';
        foreach ($this->dataMap->getPropertyList() as $map) {
            if ($map->getPropertyName() == 'modified' || $map->getPropertyName() == 'modifiedDate') {
                $now = Tk_Type_Date::createDate();
                $result .= '`' . $map->getColumnName() . "` = '" . $now->getIsoDate(true) . "',";
            } else {
                $result .= '`' . $map->getColumnName() . "` = " . $this->getSqlColumnValue($map, $row) . ",";
            }
        }
        $str = substr($result, 0, -1);
        return $str;
    }
    
    /**
     * Get the insert text for a query
     *
     * @param mixed $obj
     * @return string
     */
    function getInsertList($obj)
    {
        $columns = '';
        $values = '';
        $row = Tk_Loader_Factory::getInstance()->getObjectValues($obj);
        
        foreach ($this->dataMap->getIdPropertyList() as $map) {
            $columns .= "`" . $map->getColumnName() . '`,';
            $values .= $this->getSqlColumnValue($map, $row) . ",";
        }
        foreach ($this->dataMap->getPropertyList() as $map) {
            $columns .= "`" . $map->getColumnName() . '`,';
            $values .= $this->getSqlColumnValue($map, $row) . ",";
        }
        return '(' . substr($columns, 0, -1) . ') VALUES(' . substr($values, 0, -1) . ')';
    }
    
    /**
     * Get the string representation of the data
     *
     * @param Dk_Mapper_ColumnMap $map
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
     * @param string $where EG: "`column1`=4 AND `column2`=string"
     * @param Tk_Db_Tool $tool
     * @param integer $prepend Used for table aliases in a query
     * @param boolean $isDistinct
     * @param string $groupBy
     * @return Tk_Loader_Collection
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
        //vd($query);
        $result = $this->db->query($query);
        return $this->makeCollection($result, $tool);
    }
    
    /**
     * Select a number of elements from a database
     *
     * @param string $where EG: "`column1`=4 AND `column2`=string"
     * @param Tk_Db_Tool $tool
     * @return Tk_Loader_Collection
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
        $idFields = $this->dataMap->getIdPropertyList();
        $idField = current($idFields);
        if ($idField == null) {
            throw new Tk_ExceptionNullPointer('No Primary Id prperties set in the data mapper.');
        }
        $query = sprintf('SELECT %s FROM `%s` WHERE `%s` = %d LIMIT 1', $this->getSelectList(), $this->getTable(), $idField->getColumnName(), intval($id));
        
        $result = $this->db->query($query);
        if ($result->count() > 0) {
            $obj = Tk_Loader_Factory::loadObject($result->current(), $this->dataMap);
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
        $this->db->query($query);
        $id = $this->db->getInsertID();
        $obj->id = $id;
        if ($this->dataMap->getPropertyMap('orderBy')) {
            $this->updateValue($id, 'orderBy', $id);
        }
        return $id;
    }
    
    /**
     * Update this object in the database.
     * Returns The number of affected rows.
     *
     * @param Dk_Object $obj
     * @return integer The number of affected rows
     */
    function update($obj)
    {
        $idFields = $this->dataMap->getIdPropertyList();
        $idField = current($idFields);
        $query = sprintf('UPDATE `%s` SET %s WHERE `%s` = %d', $this->getTable(), $this->getUpdateList($obj), $idField->getColumnName(), $obj->getId());
        $this->db->query($query);
        return $this->db->getAffectedRows();
    }
    
    /**
     * Update a single value in a single row
     *
     * @param integer $id
     * @param string $column
     * @param mixed $value
     * @return integer Return the number of rows affected
     */
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
     * @param Dk_Object $obj
     * @return integer
     */
    function delete($obj)
    {
        $objectValues = Tk_Loader_Factory::getInstance()->getObjectValues($obj);
        
        $where = '';
        /* @var $map Tk_Db_ColumnMap */
        foreach ($this->dataMap->getIdPropertyList() as $map) {
            $where .= '`' . $map->getColumnName() . '` = ' . $objectValues[$map->getPropertyName()] . ',';
        }
        $where = substr($where, 0, -1);
        $query = sprintf('DELETE FROM `%s` WHERE %s LIMIT 1', $this->getTable(), $where);
        $this->db->query($query);
        return $this->db->getAffectedRows();
    }
    
    /**
     * Delete an array of Ids from the database
     *
     *
     * @param array $ids
     * @return integer The number of affected rows.
     */
    function deleteGroup($ids)
    {
        $where = '';
        /* @var $map Tk_Db_ColumnMap */
        foreach ($ids as $id) {
            $where .= '`id` = ' . intval($id) . ' OR ';
        }
        $where = substr($where, 0, -3);
        $query = sprintf('DELETE FROM `%s` WHERE %s LIMIT 1', $this->getTable(), $where);
        $this->db->query($query);
        return $this->db->getAffectedRows();
    }
    
    /**
     * Returns the object id if it is greater than 0 or the nextInsertId if is 0
     *
     * @return integer
     */
    function getVolitileId($obj)
    {
        if ($obj->getId() == 0) {
            $id = $this->db->getNextInsertId($this->getTable());
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
        
        $result = $this->db->query($query);
        $value = $result->current();
        return intval($value['i'], 10);
    }
    
    /**
     * Swap the order of 2 records
     *
     * @param Tk_Db_Object $fromObj
     * @param Tk_Db_Object $toObj
     */
    function orderSwap($fromObj, $toObj)
    {
        if (!$this->dataMap->getPropertyMap('orderBy')) {
            return;
        }
        $idFields = $this->dataMap->getIdPropertyList();
        $idField = current($idFields);
        
        $query = sprintf("UPDATE `%s` SET `orderBy` = '%s' WHERE `%s` = %d", $this->getTable(), $toObj->getOrderBy(), $idField->getColumnName(), $fromObj->getId());
        $this->getDb()->query($query);
        $query = sprintf("UPDATE `%s` SET `orderBy` = '%s' WHERE `%s` = %d", $this->getTable(), $fromObj->getOrderBy(), $idField->getColumnName(), $toObj->getId());
        $this->getDb()->query($query);
        
        return $this->getDb()->getAffectedRows();
    }
    
    /**
     * Reset the order values to id values.
     *
     */
    function resetOrder()
    {
        if (!$this->dataMap->getPropertyMap('orderBy')) {
            return;
        }
        $query = sprintf("UPDATE `%s` SET `orderBy` = `id`", $this->getTable());
        $this->getDb()->query($query);
        return $this->getDb()->getAffectedRows();
    }
}
