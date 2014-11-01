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
 *   $db = new Tk_Db_MyDao('user', 'pass', 'db', 'host');
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
 * @package Tk
 */
class Tk_Db_MyDao extends Tk_Object
{
    
    /**
     * This holds a php database resource returned by mysqli_pconnect()
     *
     * @var resource
     */
    private $res = null;
    
    private $dbName = '';
    
    protected $queryTime = 0;
    
    private $lastQuery = '';
    
    /**
     *
     *
     * @param string $user The db server user
     * @param string $pass The db server user password
     * @param string $host (optional)The hostname for db server, default = 'localhost'
     * @throws Tk_Db_ExceptionSql
     */
    function __construct($user, $pass, $host = 'localhost')
    {
        $this->res = mysqli_connect($host, $user, $pass);
        if (!$this->res) {
            throw new Tk_ExceptionSql("Could not connect to database server." . $this->getError(), E_USER_ERROR);
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
            mysqli_close($this->res);
        }
    }
    
    /**
     * Select a new Db
     *
     * @param string $dbName
     */
    function selectDb($dbName)
    {
        if (!mysqli_select_db($this->res, $dbName)) {
            throw new Tk_ExceptionSql("Select database '$dbName': " . $this->getError());
        }
        $this->resName = $dbName;
    }
    
    /**
     * For SELECT queries.
     *
     * @param string $sql The SQL query string.
     * @return Tk_Db_MyResult
     * @throws Tk_Db_ExceptionSql
     */
    function query($sql)
    {
        $a = microtime(true);
        $queryResource = mysqli_query($this->res, $sql);
        if (!$queryResource) {
            $e = new Tk_ExceptionSql($this->getError());
            $e->setDump($sql);
            throw $e;
        }
        $this->lastQuery = $sql;
        $result = new Tk_Db_MyResult($this, $queryResource);
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
        $total = 0;
        if (preg_match('/^SELECT/i', $sql)) {
            $cSql = preg_replace('/(LIMIT [0-9]+(( )?, [0-9]+)?)?/i', '', $sql);
            $countSql = "SELECT COUNT(*) as i FROM ($cSql) as t";
            $res = mysqli_query($this->res, $countSql);
            if ($this->getError()) {
                $e = new Tk_ExceptionSql($this->getError());
                $e->setDump($countSql);
                throw $e;
            }
            if ($res) {
                $row = mysqli_fetch_assoc($res);
                $total = (int)$row['i'];
            }
        }
        return $total;
    }
    
    /**
     * Execute multiple queries in one string
     *
     * @param string $sqlString
     * @return Tk_Db_MyResult[]
     */
    function multiQuery($sqlString)
    {
        $resultList = array();
        $sqlString = preg_replace("(--.*)", '', $sqlString);
        $queryList = preg_split('/\.*;\s*\n\s*/', $sqlString);
        if (!is_array($queryList) || count($queryList) == 0) {
            $e = new Tk_ExceptionSql('Error in SQL query data');
            throw $e;
        }
        foreach ($queryList as $query) {
            $query = trim($query);
            if (!$query) {
                continue;
            }
            $resultList[] = $this->query($query);
        }
        return $resultList;
    }
    
    /**
     * Check if a database with the supplied name exists
     *
     * @param string $dbName
     * @return boolean
     */
    function databaseExists($dbName)
    {
        $dbName = self::escapeString($dbName);
        $sql = sprintf("SHOW DATABASES LIKE '%s'", $dbName);
        $result = $this->query($sql);
        if ($this->getError()) {
            $e = new Tk_ExceptionSql($this->getError());
            $e->setDump($sql);
            throw $e;
        }
        foreach ($result as $v) {
            $k = sprintf('Database (%s)', $dbName);
            if ($v[$k] == $dbName) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if a database with the supplied name exists
     *
     * @param string $tableName
     * @return boolean
     */
    function tableExists($tableName)
    {
        $tableName = self::escapeString($tableName);
        $sql = sprintf("SHOW TABLES LIKE '%s'", $tableName);
        $result = $this->query($sql);
        if ($this->getError()) {
            $e = new Tk_ExceptionSql($this->getError());
            $e->setDump($sql);
            throw $e;
        }
        
        foreach ($result as $v) {
            $k = sprintf('Tables_in_%s (%s)', $this->getDbName(), $tableName);
            if ($v[$k] == $tableName) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get an array containing all the avalible databases to the user
     *
     * @return array
     */
    function getDatabaseList()
    {
        $sql = "SHOW DATABASES";
        $result = $this->query($sql);
        if ($this->getError()) {
            $e = new Tk_ExceptionSql($this->getError());
            $e->setDump($sql);
            throw $e;
        }
        $list = array();
        foreach ($result as $row) {
            $list[] = $row['Database'];
        }
        return $list;
    }
    
    /**
     * Get an array containing all the table names for this DB
     *
     * @return array
     */
    function getTableList()
    {
        $sql = "SHOW TABLES";
        $result = $this->query($sql);
        if ($this->getError()) {
            $e = new Tk_ExceptionSql($this->getError());
            $e->setDump($sql);
            throw $e;
        }
        $list = array();
        foreach ($result as $row) {
            $list[] = $row['Tables_in_' . $this->getDbName()];
        }
        return $list;
    }
    
    /**
     * Get the insert id of the last added record.
     * Taken From: http://dev.mysql.com/doc/refman/5.0/en/innodb-auto-increment-handling.html
     *
     * @return integer The next assigned integer to the primary key
     */
    function getNextInsertId($tableName)
    {
        $query = sprintf("SHOW TABLE STATUS LIKE '%s' ", $tableName);
        $result = mysqli_query($this->res, $query);
        $row = mysqli_fetch_array($result);
        if ($row['Auto_increment'] > 0) {
            return intval($row['Auto_increment']);
        }
        $query = sprintf("SELECT MAX(`id`) AS `lastId` FROM `%s` ", $tableName);
        $result = mysqli_query($query);
        $row = mysqli_fetch_array($result);
        return intval($row['lastId']) + 1;
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
        return mysqli_insert_id($this->res);
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
        return mysqli_affected_rows($this->res);
    }
    
    /**
     * Get any MySQL errors.
     *
     * @return string Returns a MySQL server error
     */
    function getError()
    {
        if ($this->res) {
            return mysqli_error($this->res);
        }
        return mysqli_error();
    }
    
    /**
     * Encode characters to avoid sql injections.
     *
     * @param string $str
     */
    static function escapeString($str)
    {
        if (Tk_Db_Factory::getDb()->res) {
            $str = mysqli_real_escape_string(Tk_Db_Factory::getDb()->res, $str);
        } else {
            $str = mysqli_real_escape_string($str);
        }
        return $str;
    }
}