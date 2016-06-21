<?php


namespace App\Db;

/**
 * Class Data
 * 
 * 
 *    NOTE NOTE NOTE:   The below has just been proven wrong... There must be something else ???????????????
 *                      Check if we can remove the cleanKey() method.....
 * 
 * Note: When accessing data using the key name, any `-` or `_` characters are replaced to a `.`
 *   This is for when accepting data from forms, because field names cannot use the `.` char
 *   Thus 
 *      `system_name_value` becomes `system.name.value` 
 *   or
 *      `system-name-value` becomes `system.name.value`
 * 
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Data extends \Tk\Collection
{
    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $table = '';
    
    /**
     * @var int
     */
    protected $foreignId = 0;
    
    /**
     * @var string
     */
    protected $foreignKey = '';


    /**
     * Data constructor.
     *
     * @param int $foreignId
     * @param string $foreignKey
     * @param string $table (optional) Default: `data`
     * @param \Tk\Db\Pdo|null $db
     */
    public function __construct($foreignId = 0, $foreignKey ='system', $table = 'data', $db = null)
    {
        parent::__construct([]);
        if (!$db) {
            $db = \App\Factory::getDb();      // @dependency
        }
        $this->db = $db;
        $this->table = $table;
        $this->foreignId = $foreignId;
        $this->foreignKey = $foreignKey;
        $this->loadData();
    }

    /**
     * @return string
     */
    protected function getTable()
    {
        return $this->db->quoteParameter($this->table);
    }

    /**
     * Load this object with all available data
     * 
     * @return $this
     */
    public function loadData()
    {
        $sql = sprintf('SELECT * FROM %s WHERE foreign_id = %d AND foreign_key = %s ', $this->getTable(), 
            (int)$this->foreignId, $this->db->quote($this->foreignKey));
        $stmt = $this->db->query($sql);
        $stmt->setFetchMode(\PDO::FETCH_OBJ);
        foreach ($stmt as $row) {
            $this->set($row->key, $row->value);
        }
        return $this;
    }

    /**
     * Save object data to the DB
     * 
     * @return $this
     */
    public function saveData()
    {
        foreach($this as $k => $v) {
            $this->dbSet($k, $v);
        }
        return $this;
    }

    /**
     * set a single data value in the Database 
     * 
     * @param $key
     * @param $value
     * @return Data
     */
    protected function dbSet($key, $value)
    {
        if (is_array($value) || is_object($value)) {
            return false;
        }
        if ($this->dbHas($key)) {
            $sql = sprintf('UPDATE %s SET value = %s WHERE key = %s AND foreign_id = %d AND foreign_key = %s ', 
                $this->getTable(), $this->db->quote($value), $this->db->quote($key), 
                (int)$this->foreignId, $this->db->quote($this->foreignKey) );
        } else {
            $sql = sprintf('INSERT INTO %s (foreign_id, foreign_key, key, value) VALUES (%d, %s, %s, %s)', 
                $this->getTable(), (int)$this->foreignId, $this->db->quote($this->foreignKey),
                $this->db->quote($key), $this->db->quote($value));
        }
        $this->db->exec($sql);
        return $this;
    }

    /**
     * Get a value from the database
     * 
     * @param $key
     * @return string
     */
    protected function dbGet($key)
    {
        $sql = sprintf('SELECT * FROM %s WHERE `key` = %s AND foreign_id = %d, foreign_key = %s ', $this->getTable(),  
            $this->db->quote($key), (int)$this->foreignId, $this->db->quote($this->foreignKey));
        $row = $this->db->query($sql)->fetchObject();
        if ($row) {
            return $row->value;
        }
        return '';
    }

    /**
     * Check if a value exists in the DB
     * 
     * @param $key
     * @return bool
     */
    protected function dbHas($key)
    {
        $sql = sprintf('SELECT * FROM %s WHERE `key` = %s AND foreign_id = %d, foreign_key = %s ', $this->getTable(),
            $this->db->quote($key), (int)$this->foreignId, $this->db->quote($this->foreignKey));
        $row = $this->db->query($sql)->fetchObject();
        if ($row) return true;
        return false;
    }

    /**
     * Remove a value from the DB
     * 
     * @param $key
     * @return $this
     */
    protected function dbDelete($key)
    {
        $sql = sprintf('DELETE FROM %s WHERE `key` = %s AND foreign_id = %d, foreign_key = %s  ', $this->getTable(), 
            $this->db->quote($key), (int)$this->foreignId, $this->db->quote($this->foreignKey));
        $this->db->exec($sql);
        return $this;
    }
    
    
    
    // SEE THE NOTES ABOVE, THIS MAY NOT BE NEEDED.
    
    // Fix keys: (one-key to one.key) or (one_key to one.key)
    

    /**
     * Set an item in the collection
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        return parent::set($this->cleanKey($key), $value);
    }

    /**
     * Get collection item for key
     *
     * @param $key
     * @param null|mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return parent::get($this->cleanKey($key), $default);
    }
    
    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     * @return bool
     */
    public function has($key)
    {
        return parent::has($this->cleanKey($key));
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     * @return $this
     */
    public function remove($key)
    {
        return parent::remove($this->cleanKey($key));
    }
    
    /**
     * @param string $key
     * @return string 
     */
    protected function cleanKey($key)
    {
        return preg_replace('/[_-]/', '.', $key);
    }
    
    
}