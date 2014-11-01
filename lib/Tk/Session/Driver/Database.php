<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A session object
 *
 * <code>
 *    CREATE TABLE session (
 *       `id` VARCHAR(127) NOT NULL,
 *       `data` TEXT NOT NULL,
 *       `modified` INT(10) UNSIGNED NOT NULL,
 *       `created` INT(10) UNSIGNED NOT NULL,
 *       PRIMARY KEY (`id`)
 *   );
 *  </code>
 *
 *
 * @package Tk
 */
class Tk_Session_Driver_Database implements Tk_Session_Driver_Interface
{
    
    /**
     * @var Tk_Db_MyDao
     */
    protected $db = 'default';
    protected $table = 'session';
    
    // Encryption
    protected $encrypt = false;
    
    // Session settings
    protected $sessionId = null;
    protected $written = false;
    
    /**
     * Create a Database session
     */
    function __construct()
    {
        Tk::loadConfig('tk.session');
        $this->encrypt = Tk_Config::get('tk.session.encryption');
        if (Tk_Config::exists('tk.session.database.table')) {
            $this->table = Tk_Config::get('tk.session.database.table');
        }
        $this->db = Tk_Db_Factory::getDb(Tk_Config::get('tk.session.database.group'));
        Tk::log('Session Database Driver Initialized');
    }
    
    /**
     * Open the session
     *
     * @param string $path
     * @param string $name
     * @return boolean
     */
    function open($path, $name)
    {
        return true;
    }
    
    /**
     * close
     *
     * @return boolean
     */
    function close()
    {
        return true;
    }
    
    /**
     * read
     *
     * @param string $id
     */
    function read($id)
    {
        // Load the session
        $query = sprintf('SELECT * FROM `%s` WHERE `id` = %s LIMIT 1', $this->table, enquote($id));
        $result = $this->db->query($query);
        $row = $result->current();
        if (!$row) {
            // No current session
            $this->sessionId = null;
            return '';
        }
        // Set the current session id
        $this->sessionId = $id;
        // Load the data
        $data = $row['data'];
        return ($this->encrypt) ? base64_decode($data) : Tk_Util_Encrypt::decrypt($data);
    }
    
    /**
     * write
     *
     * @param string $id
     * @param string $data
     */
    function write($id, $data)
    {
        $result = null;
        $data = ($this->encrypt) ? base64_encode($data) : Tk_Util_Encrypt::encrypt($data);
        if ($this->sessionId === null) {
            // Insert a new session
            $query = sprintf('INSERT INTO `%s` VALUES (%s, %s, %s, %s)', $this->table, enquote($id), enquote($data), enquote(time()), enquote(time()));
            $result = $this->db->query($query);
        } elseif ($id === $this->sessionId) {
            // Update the existing session
            $query = sprintf("UPDATE `%s` SET `modified` = %s, `data` = %s WHERE `id` = %s", $this->table, enquote(time()), enquote($data), enquote($id));
            $result = $this->db->query($query);
        } else {
            // Update the session and id
            $query = sprintf("UPDATE `%s` SET `id` = %s, `modified` = %s, `data` = %s WHERE `id` = %s", $this->table, enquote($id), enquote(time()), enquote($data), enquote($this->sessionId));
            $result = $this->db->query($query);
            // Set the new session id
            $this->sessionId = $id;
        }
        
        return (bool)$this->db->getAffectedRows();
    }
    
    /**
     * destroy
     *
     * @param string $id
     */
    function destroy($id)
    {
        $query = sprintf('DELETE FROM `%s` WHERE `id` = %s LIMIT 1', $this->getTable(), enquote($id));
        $this->db->query($query);
        $this->sessionId = null;
        return true;
    }
    
    /**
     * regenerate and return new session id
     *
     * @return string
     */
    function regenerate()
    {
        session_regenerate_id();
        return sessionId();
    }
    
    /**
     * garbage collect
     *
     * @param integer $maxlifetime
     */
    function gc($maxlifetime)
    {
        // Delete all expired sessions
        $query = sprintf('DELETE FROM `%s` WHERE `modified` < %s LIMIT 1', $this->table, enquote(time() - $maxlifetime));
        $this->db->query($query);
        if ($this->db->getError()) {
            return false;
        }
        Tk::log('Session garbage collected');
        return true;
    }

}