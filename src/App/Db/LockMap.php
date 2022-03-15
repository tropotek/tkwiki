<?php
namespace App\Db;

use Bs\Db\User;
use Tk\ConfigTrait;

/**
 * Class LockMap
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class LockMap
{
    use ConfigTrait;

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
                $db = \App\Config::getInstance()->getDb();
            }
            self::$instance = new static($user, $db);
        }
        return self::$instance;
    }

    /**
     * @param $pageId
     * @return string
     */
    public function getPageHash($pageId)
    {
        //$sessionId = $this->getConfig()->getSession()->getName();
        $sessionId = $this->getConfig()->getSession()->getId();
        //vd($this->user->getId(), $this->getConfig()->getSession()->getName(), $this->getConfig()->getSession()->getId());
        //return md5($pageId . $this->user->getId() . $this->user->getIp());
        //vd($pageId  . $this->user->getId() . $sessionId);
        return md5($pageId  . $this->user->getId() . $sessionId);
    }


    /**
     * lock a wiki page if the user has access to the lock
     *
     * @param int $pageId
     * @return bool
     * @throws \Exception
     */
    public function lock($pageId)
    {
        if (!$this->canAccess($pageId)) return false;

        $expire = \Tk\Date::create(time()+$this->timeout);
        if ($this->isLocked($pageId)) {
            if ($this->ownLock($pageId)) {
                $sql = sprintf('UPDATE %s SET expire = %s WHERE hash = %s', $this->db->quoteParameter('lock'),
                    $this->db->quote($expire->format(\Tk\Date::FORMAT_ISO_DATE)), $this->db->quote($this->getPageHash($pageId)));
                $this->db->exec($sql);
            }
        } else {
            $sql = sprintf('INSERT INTO %s VALUES (%s, %d, %d, %s, %s)', $this->db->quoteParameter('lock'),
                $this->db->quote($this->getPageHash($pageId)),
                $pageId, $this->user->getId(), $this->db->quote($this->user->getIp()),
                $this->db->quote($expire->format(\Tk\Date::FORMAT_ISO_DATE)) );
            $this->db->exec($sql);
        }
        return true;
    }

    /**
     * remove the lock
     *
     * @param $pageId
     * @return bool
     * @throws \Exception
     */
    function unlock($pageId)
    {
        if (!$this->canAccess($pageId)) return true;

        $sql = sprintf('DELETE FROM %s WHERE hash = %s',
            $this->db->quoteParameter('lock'), $this->db->quote($this->getPageHash($pageId)));
        $this->db->exec($sql);
    }

    /**
     * remove the lock
     *
     * @param $pageId
     * @return bool
     * @throws \Exception
     */
    function clearUserLocks($userId)
    {
        $sql = sprintf('DELETE FROM %s WHERE user_id = %s',
            $this->db->quoteParameter('lock'), (int)$userId);
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
     * @throws \Exception
     */
    public function canAccess($pageId)
    {
        if ($pageId <= 0) return false;
        if (!$this->isLocked($pageId)) {
            return true;
        }else if ($this->ownLock($pageId)) {
            return true;
        }
        return false;
    }

    /**
     * Is the page locked
     *
     * @param int $pageId
     * @return boolean
     * @throws \Exception
     */
    public function isLocked($pageId)
    {
        $this->clearExpired();
        $sql = sprintf('SELECT COUNT(*) as i FROM %s WHERE page_id = %d', $this->db->quoteParameter('lock'), $pageId);
        $res = $this->db->query($sql);
        $row = $res->fetch();
        return ($row->i > 0);
    }

    /**
     * Does the userId own the lock
     *
     * @param int $pageId
     * @return boolean
     * @throws \Exception
     */
    public function ownLock($pageId)
    {
        $sql = sprintf('SELECT COUNT(*) as i FROM %s WHERE hash = %s',
            $this->db->quoteParameter('lock'), $this->db->quote($this->getPageHash($pageId)));
        $res = $this->db->query($sql);
        $row = $res->fetch();
        return ($row->i > 0);
    }

    /**
     * occasionally look at clearing the expired locks
     * The default time to check is 2 * timeout
     *
     * @throws \Exception
     */
    public function clearExpired()
    {
        static $last = null;
        $now = time();
        if ( !$last || ($now - $last) > ($this->timeout*2) ) {
            $sql = sprintf('DELETE FROM %s WHERE expire < %s ',
                $this->db->quoteParameter('lock'), $this->db->quote(\Tk\Date::create()->format(\Tk\Date::FORMAT_ISO_DATE)));
            $this->db->exec($sql);
        }
    }
}
