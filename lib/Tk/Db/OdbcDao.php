<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Base data access object for MySQL database.<br>
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
class Tk_Db_OdbcDao extends Tk_Object
{
    
    /**
     * This holds a php database resource returned by odbc_pconnect()
     * @var resource $res
     */
    private $res = null;
    
    private $dbName = '';
    
    protected $queryTime = 0;
    
    private $lastQuery = '';
    private $lastQueryRes = null;
    
    
    /**
     *
     *
     * @param string $user The db server user
     * @param string $pass The db server user password
     * @param string $host The MS SQL server. It can also include a port number. e.g. hostname,port or 'KALLESPC\SQLEXPRESS'. 
     * @throws Tk_Db_ExceptionSql
     */
    function __construct($user, $pass, $host)
    {
        $this->res = odbc_connect($host, $user, $pass);
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
            odbc_close($this->res);
        }
    }
    
    /**
     * Select a new Db, not needed for odbc only left here for interface compatablity
     *
     * @param string $dbName
     * @throws Tk_Db_ExceptionSql
     */
    function selectDb($dbName)
    {
//        if (!odbc_select_db($dbName, $this->res)) {
//            throw new Tk_ExceptionSql("Select database '$dbName': " . $this->getError());
//        }

        // Use this...
        //odbc_exec($conn,'use different_DB');

        $this->resName = $dbName;
    }
    
    /**
     * For SELECT queries.
     *
     * @param string $sql The SQL query string.
     * @return Tk_Db_OdbcResult
     * @throws Tk_Db_ExceptionSql
     */
    function query($sql)
    {
        $a = microtime(true);
        $this->lastQueryRes = @odbc_exec($this->res, $sql);
        
//        $query = odbc_prepare($this->res, $sql);
//        $this->lastQueryRes = odbc_execute($query);
        
        if (!$this->lastQueryRes) {
            $e = new Tk_ExceptionSql($this->getError());
            $e->setDump($sql);
            throw $e;
        }
        $this->lastQuery = $sql;
        $result = new Tk_Db_OdbcResult($this, $this->lastQueryRes);
        $this->queryTime = round((microtime(true) - $a), 4);
        return $result;
    }
    
    /**
     * Count a query and return the total possible results
     *
     * @param string $sql
     * @return integer
     * @throws Tk_Db_ExceptionSql
     * @todo Test if this works for ODBC
     */
    function countQuery($sql)
    {
//        $total = 0;
//        if (eregi('^SELECT', $sql)) {
//            $cSql = ereg_replace('(LIMIT [0-9]+(( )?, [0-9]+)?)?', '', $sql);
//            $countSql = "SELECT COUNT(*) as i FROM ($cSql) as t";
//            $this->lastQueryRes = odbc_exec($this->res, $countSql);
//            if (!$this->lastQueryRes) {
//                throw new Tk_ExceptionSql($this->getError());
//            }
//            $row = odbc_fetch_array($this->lastQueryRes);
//            $total = (int)$row['i'];
//            
//        }
//        return $total;
    }
    
    /**
     * Execute multiple queries in one string
     *
     * @param string $sqlString
     * @return Tk_Db_MyResult[]
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
     * @todo Get this working for ODBC
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
     * @todo Get this working for ODBC
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
     * @todo Get this working for ODBC
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
     * @todo Get this working for ODBC
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
     * 
     * @return integer The next assigned integer to the primary key
     * @todo Get this working for ODBC
     */
    function getNextInsertId($tableName)
    {
//        $query = sprintf("SHOW TABLE STATUS LIKE '%s' ", $tableName);
//        $result = mysql_query($query);
//        $row = mysql_fetch_array($result);
//        if ($row['Auto_increment'] > 0) {
//            return intval($row['Auto_increment']);
//        }
//        $query = sprintf("SELECT MAX(`id`) AS `lastId` FROM `%s` ", $tableName);
//        $result = mysql_query($query);
//        $row = mysql_fetch_array($result);
//        return intval($row['lastId']) + 1;
        return 0;
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
     * Get the last executed query resource
     *
     * @return resource
     */
    function getLastQueryRes()
    {
        return $this->lastQueryRes;
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
     * @todo: clean-up
     */
    function getInsertID()
    {
        $res = $this->query('select SCOPE_IDENTITY() AS last_insert_id');
        if ($res) {
            $row = odbc_fetch_array($res);
            $i = (int)$row['last_insert_id'];
            $res->free();
            return $i;
        }
        return 0;
    }
    
    /**
     * Get the number of modified rows with last query.
     * NOTE:
     *   Not always correct due to mysql update bug/feature
     *
     * @return integer
     */
    function getAffectedRows()
    {
//        ob_start(); // block printing table with results
//        (int)$rows = odbc_result_all($this->lastQueryRes);
//        ob_clean(); // block printing table with results
        return $rows;
    }
    
    /**
     * Get any MySQL errors.
     *
     * @return string Returns a MySQL server error
     */
    function getError()
    {
        return odbc_errormsg();
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