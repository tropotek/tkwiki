<?php
namespace App\Db;

/**
 * Class LockMap
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class LockMap
{
    /**
     * @var LockMap
     */
    static private $instance = null;

    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;

    /**
     * @var User
     */
    protected $user = null;

    /**
     * @var int
     */
    protected $timeout = 120;
    
    
    /**
     * 
     * @param User $user
     * @param \Tk\Db\Pdo $db
     */
    public function __construct($user, $db)
    {
        $this->user = $user;
        $this->db = $db;
    }

    /**
     *
     * @param User $user
     * @param \Tk\Db\Pdo $db
     * @return LockMap
     */
    static function getInstance($user = null, $db = null)
    {
        if (!self::$instance) {
            if (!$db) {
                $db = \App\Factory::getDb();
            }
            self::$instance = new static($user, $db);
        }
        return self::$instance;
    }
    
    

    /**
     * lock a wiki page if the user has access to the lock
     *
     * @param int $pageId
     * @return bool
     */
    public function lock($pageId)
    {
        if (!$this->canAccess($pageId)) return false;
        
        $expire = \Tk\Date::create(time()+$this->timeout);
        if ($this->isLocked($pageId)) {
            if ($this->hasLock($pageId)) {
                $sql = sprintf('UPDATE lock SET expire = %s WHERE hash = %s',
                    $this->db->quote($expire->format(\Tk\Date::ISO_DATE)), $this->db->quote(md5($pageId . $this->user->id . $this->user->ip)));
                $this->db->exec($sql);
            }
        } else {
            $sql = sprintf('INSERT INTO lock VALUES (%s, %d, %d, %s, %s)',
                $this->db->quote(md5($pageId . $this->user->id . $this->user->ip)),
                $pageId, $this->user->id, $this->db->quote($this->user->ip),
                $this->db->quote($expire->format(\Tk\Date::ISO_DATE)) );
            $this->db->exec($sql);
        }
        return true;
    }

    /**
     * remove the lock
     *
     * @param $pageId
     * @return bool
     */
    function unlock($pageId)
    {
        if (!$this->canAccess($pageId)) return true;

        $sql = sprintf('DELETE FROM lock WHERE hash = %s', $this->db->quote(md5($pageId . $this->user->id . $this->user->ip)));
        $this->db->exec($sql);
    }

    /**
     * Can the current user
     *  - lock the requested page
     *  - access the currently locked page
     * 
     * Call this to see if the user can access the lock for a page.
     * 
     * @param $pageId
     * @return bool
     */
    public function canAccess($pageId)
    {
        if ($pageId <= 0) return false;
        if (!$this->isLocked($pageId)) {
            return true;
        } else if ($this->hasLock($pageId)) {
            return true;
        }
        return false;
    }




    /**
     * Enter description here...
     *
     * @param int $pageId
     * @return boolean
     */
    public function isLocked($pageId)
    {
        $this->clearExpired();
        $sql = sprintf('SELECT COUNT(*) as i FROM lock WHERE page_id = %d', $pageId);
        $res = $this->db->query($sql);
        $row = $res->fetch();
        return ($row->i > 0);
    }
    /**
     * Does the userId own the lock
     *
     * @param int $pageId
     * @return boolean
     */
    public function hasLock($pageId)
    {
        $sql = sprintf('SELECT COUNT(*) as i FROM lock WHERE hash = %s', $this->db->quote(md5($pageId . $this->user->id . $this->user->ip)));
        $res = $this->db->query($sql);
        $row = $res->fetch();
        return ($row->i > 0);
    }

    /**
     * occasionally look at clearing the expired locks
     * The default time to check is 2 * timeout
     *
     */
    public function clearExpired()
    {
        static $last = null;
        $now = time();
        if ( !$last || ($now - $last) > ($this->timeout*2) ) {
            $sql = sprintf('DELETE FROM lock WHERE expire < %s', $this->db->quote(\Tk\Date::create()->format(\Tk\Date::ISO_DATE)));
            $this->db->exec($sql);
        }
    }
}