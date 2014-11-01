<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Base data access object for MsSQL database.<br>
 *
 * usage:<br>
 * <code>
 * <?php
 *   $db = new Tk_Db_MsDao('user', 'pass', 'db', 'host');
 *   $sql = "SELECT * FROM table";
 *   $result = $db->query($sql);
 *   while($row = $result->getRow()) {
 *       foreach($row as $k => $e) {
 *           echo $e.", ";
 *       }
 *   }
 * ?>
 * </code>
 *
 * @package Db
 */
class Tk_Db_MsDao extends Tk_Object
{
    
    /**
     * This holds a php database resource returned by mssql_pconnect()
     *
     * @var resource $db
     */
    private $res = null;
    
    private $dbName = '';
    
    protected $queryTime = 0;
    
    private $lastQuery = '';
    
    /**
     * EG: $cfg['host'], $cfg['name'], $cfg['user'], $cfg['password']
     *
     * @param array $cfg
     * @throws Tk_Db_ExceptionSql
     */
    function __construct($cfg)
    {
        $this->dbName = $cfg['name'];
        $con = array("Database" => $this->dbName, "ReturnDatesAsStrings" => true);
        if (!empty($cfg['user'])) {
            $con['UID'] = $cfg['user'];
        }
        if (!empty($cfg['password'])) {
            $con['PWD'] = $cfg['password'];
        }
        $this->res = sqlsrv_connect($cfg['host'], $con);
        if (!$this->res) {
            throw new Tk_ExceptionSql("Could not connect to database server." . $this->getError());
        }
    }
    
    /**
     * Clean up database.
     *
     */
    function __destruct()
    {
        $this->close();
    }
    
    /**
     * close
     *
     */
    function close()
    {
        if (is_resource($this->res)) {
            sqlsrv_close($this->res);
        }
    }
    
    /**
     * Select a new Db
     *
     * @param string $dbName
     */
    function selectDb($dbName)
    {
//        if (!mssql_select_db($dbName, $this->res)) {
//            throw new Tk_ExceptionSql("Select database '$dbName': " . $this->getError());
//        }
//        $this->resName = $dbName;
    }
    
    /**
     * For SELECT queries.
     *
     * @param string $sql The SQL query string.
     * @return Tk_Db_MsResult
     * @throws Tk_Db_ExceptionSql
     */
    function query($sql)
    {
        $a = microtime(true);
        // SQLSRV_CURSOR_STATIC
        $queryResource = sqlsrv_query($this->res, $sql, null, array('Scrollable' => SQLSRV_CURSOR_KEYSET ) );
        if (!$queryResource) {
            $e = new Tk_ExceptionSql($this->getError());
            $e->setDump($sql);
            throw $e;
        }
        $this->lastQuery = $sql;
        $result = new Tk_Db_MsResult($this, $queryResource);
        $this->queryTime = round((microtime(true) - $a), 4);
        return $result;
    }
    
    /**
     * Count a query and return the total possible results
     *
     * @param string $sql
     * @return integer
     */
    function countQuery($sql)
    {
//        $total = 0;
//        if (eregi('^SELECT', $sql)) {
//            $cSql = ereg_replace('(LIMIT [0-9]+(( )?, [0-9]+)?)?', '', $sql);
//            $countSql = "SELECT COUNT(*) as i FROM ($cSql) as t";
//            $res = sqlsrv_query($countSql, $this->res);
//            if (sqlsrv_error()) {
//                throw new Tk_ExceptionSql(mssql_error());
//            }
//            if ($res) {
//                $row = sqlsrv_fetch_assoc($res);
//                $total = (int)$row['i'];
//            }
//        }
//        return $total;
    }
    
    /**
     * Execute multiple queries in one string
     *
     * @param string $sqlString
     * @return Tk_Db_MsResult[]
     */
    function multiQuery($sqlString)
    {
//        $resultList = array();
//        $sqlString = preg_replace("(--.*)", '', $sqlString);
//        $queryList = preg_split('/\.*;\s*\n\s*/', $sqlString);
//        if (!is_array($queryList) || count($queryList) == 0) {
//            throw new Tk_ExceptionSql('Invalid sql data.');
//        }
//        foreach ($queryList as $query) {
//            $query = trim($query);
//            if (!$query) {
//                continue;
//            }
//            $resultList[] = $this->query($query);
//        }
//        return $resultList;
    }
    
    /**
     * Check if a database with the supplied name exists
     *
     * @param string $dbName
     * @return boolean
     */
    function databaseExists($dbName)
    {
//        $dbName = self::escapeString($dbName);
//        $sql = sprintf("SHOW DATABASES LIKE '%s'", $dbName);
//        $result = $this->query($sql);
//        if ($this->getError()) {
//            throw new Tk_ExceptionSql($this->getError());
//        }
//        foreach ($result as $v) {
//            $k = sprintf('Database (%s)', $dbName);
//            if ($v[$k] == $dbName) {
//                return true;
//            }
//        }
//        return false;
    }
    
    /**
     * Check if a database with the supplied name exists
     *
     * @param string $tableName
     * @return boolean
     */
    function tableExists($tableName)
    {
//        $tableName = self::escapeString($tableName);
//        $sql = sprintf("SHOW TABLES LIKE '%s'", $tableName);
//        $result = $this->query($sql);
//        if ($this->getError()) {
//            throw new Tk_ExceptionSql($this->getError());
//        }
//        foreach ($result as $v) {
//            $k = sprintf('Tables_in_%s (%s)', $this->getDbName(), $tableName);
//            if ($v[$k] == $tableName) {
//                return true;
//            }
//        }
//        return false;
    }
    
    /**
     * Get an array containing all the avalible databases to the user
     *
     * @return array
     */
    function getDatabaseList()
    {
//        $sql = "SHOW DATABASES";
//        $result = $this->query($sql);
//        if ($this->getError()) {
//            throw new Tk_ExceptionSql($this->getError());
//        }
//        $list = array();
//        foreach ($result as $row) {
//            $list[] = $row['Database'];
//        }
//        return $list;
    }
    
    /**
     * Get an array containing all the table names for this DB
     *
     * @return array
     */
    function getTableList()
    {
//        $sql = "SHOW TABLES";
//        $result = $this->query($sql);
//        if ($this->getError()) {
//            throw new Tk_ExceptionSql($this->getError());
//        }
//        $list = array();
//        foreach ($result as $row) {
//            $list[] = $row['Tables_in_' . $this->getDbName()];
//        }
//        return $list;
    }
    
    /**
     * Get the insert id of the last added record.
     * Taken From: http://dev.MsSQL.com/doc/refman/5.0/en/innodb-auto-increment-handling.html
     *
     * @return integer The next assigned integer to the primary key
     */
    function getNextInsertId($tableName)
    {
//        $query = sprintf("SHOW TABLE STATUS LIKE '%s' ", $tableName);
//        $result = mssql_query($query);
//        $row = mssql_fetch_array($result);
//        if ($row['Auto_increment'] > 0) {
//            return intval($row['Auto_increment']);
//        }
//        $query = sprintf("SELECT MAX(`id`) AS `lastId` FROM `%s` ", $tableName);
//        $result = mssql_query($query);
//        $row = mssql_fetch_array($result);
//        return intval($row['lastId']) + 1;
    }
    
    /**
     * Get the last executed query as a string
     *
     * @return string
     */
    function getLastQuery()
    {
        return $this->lastQuery;
    }
    
    /**
     * get the query execution time in milli-sec
     *
     * @return integer
     */
    function getQueryTime()
    {
        return $this->queryTime;
    }
    
    /**
     * Get the current selected DB name
     *
     * @return string
     */
    function getDbName()
    {
        return $this->resName;
    }
    
    /**
     * Get the ID generated from the previous INSERT opertation
     *
     * @return integer
     */
    function getInsertID()
    {
        $res = $this->query('select SCOPE_IDENTITY() AS last_insert_id');
        if ($res) {
            $row = sqlsrv_fetch_array($res);
            $i = (int)$row['last_insert_id'];
            $res->free();
            return $i;
        }
        return 0;
    }
    
    /**
     * Get the number of modified rows with last query.
     * NOTE:
     *   Not always correct due to MsSQL update bug/feature
     *
     * @return integer
     */
    function getAffectedRows()
    {
        return sqlsrv_rows_affected($this->res);
    }
    
    /**
     * Get any MsSQL errors.
     *
     * @return string Returns a MsSQL server error
     */
    function getError()
    {
        return print_r(sqlsrv_errors(SQLSRV_ERR_ERRORS), true);
    }
    
    /**
     * Encode characters to avoid sql injections.
     *
     * @param string $str
     */
    static function escapeString($str)
    {
        $str = str_replace("'", "''", $str);
        return $str;
    }
}