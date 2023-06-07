<?php
namespace App\Db;

use Tk\Db\Pdo;
use Tk\Traits\SystemTrait;

/**
 * This object manages page edit locking
 *
 * Pages are locked to a user when they edited to avoid overwriting another
 * users edits.
 *
 */
class Lock
{
    use SystemTrait;

    //const TIMEOUT_SEC = 60*60*2;    // Default 2 hours
    const TIMEOUT_SEC = 60*2;    // Default 2 hours

    protected User $user;


    public function __construct(User $user)
    {
        $this->user = $user;
    }

    static function create(User $user = null): Lock
    {
        return new Lock($user);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getDb(): Pdo
    {
        return $this->getFactory()->getDb();
    }

    public function getPageHash(int $pageId): string
    {
        return md5($pageId . $this->getUser()->getId());
    }


    /**
     * lock a wiki page if the user has access to the lock
     */
    public function lock(int $pageId): bool
    {
        if (!$this->canAccess($pageId)) return false;

        $expire = \Tk\Date::create(time() + self::TIMEOUT_SEC);
        if ($this->isLocked($pageId)) {
            if ($this->ownLock($pageId)) {
                $sql = sprintf('UPDATE %s SET expire = %s WHERE hash = %s',
                    $this->getDb()->quoteParameter('lock'),
                    $this->getDb()->quote($expire->format(\Tk\Date::FORMAT_ISO_DATE)),
                    $this->getDb()->quote($this->getPageHash($pageId))
                );
                $this->getDb()->exec($sql);
            }
        } else {
            $sql = sprintf('INSERT INTO %s VALUES (%s, %d, %d, %s, %s)',
                $this->getDb()->quoteParameter('lock'),
                $this->getDb()->quote($this->getPageHash($pageId)),
                $pageId,
                $this->getUser()->getId(),
                $this->getDb()->quote($this->getRequest()->getClientIp()),
                $this->getDb()->quote($expire->format(\Tk\Date::FORMAT_ISO_DATE))
            );
            $this->getDb()->exec($sql);
        }
        return true;
    }

    public function unlock(int $pageId): bool
    {
        if (!$this->canAccess($pageId)) return true;

        $sql = sprintf('DELETE FROM %s WHERE hash = %s',
            $this->getDb()->quoteParameter('lock'),
            $this->getDb()->quote($this->getPageHash($pageId))
        );
        return $this->getDb()->exec($sql);
    }

    /**
     * remove all locks for this user
     */
    public function clearAllLocks(): bool
    {
        $sql = sprintf('DELETE FROM %s WHERE user_id = %s',
            $this->getDb()->quoteParameter('lock'), $this->getUser()->getId());
        return $this->getDb()->exec($sql);
    }

    /**
     * Can the current user
     *  - lock the requested page
     *  - access the currently locked page
     *
     * Call this to see if the user can access the lock for a page.
     */
    public function canAccess(int $pageId): bool
    {
        if ($pageId <= 0) return false;
        if (!$this->isLocked($pageId)) {
            return true;
        }
        if ($this->ownLock($pageId)) {
            return true;
        }
        return false;
    }

    public function isLocked(int $pageId): bool
    {
        $this->clearExpired();
        $sql = sprintf('SELECT COUNT(*) as i FROM %s WHERE page_id = %d',
            $this->getDb()->quoteParameter('lock'),
            $pageId
        );
        $res = $this->getDb()->query($sql);
        $row = $res->fetch();
        return ($row->i > 0);
    }

    public function ownLock(int $pageId): bool
    {
        $sql = sprintf('SELECT COUNT(*) as i FROM %s WHERE hash = %s',
            $this->getDb()->quoteParameter('lock'),
            $this->getDb()->quote($this->getPageHash($pageId))
        );
        $res = $this->getDb()->query($sql);
        $row = $res->fetch();
        return ($row->i > 0);
    }

    /**
     * occasionally look at clearing the expired locks
     * The default time to check is 2 * timeout
     */
    public function clearExpired(): void
    {
        static $last = null;
        $now = time();
        if ( !$last || ($now - $last) > (self::TIMEOUT_SEC * 2) ) {
            $sql = sprintf('DELETE FROM %s WHERE expire < %s ',
                $this->getDb()->quoteParameter('lock'),
                $this->getDb()->quote(\Tk\Date::create()->format(\Tk\Date::FORMAT_ISO_DATE))
            );
            $this->getDb()->exec($sql);
        }
    }
}
