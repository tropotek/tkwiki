<?php
namespace App\Db;

use Bs\Db\User;
use Bs\Factory;
use Tk\Traits\SystemTrait;
use Tk\Db;

/**
 * This object manages page edit locking
 * Pages are locked to a user when they are edited to avoid user edits clashing.
 */
class Lock
{
    use SystemTrait;

    const TIMEOUT_SEC = 60*2;    // Default 2 minutes

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

    public function getPageHash(int $pageId): string
    {
        return md5($pageId . $this->getUser()->userId);
    }

    /**
     * lock a wiki page if the user has access to the lock
     */
    public function lock(int $pageId): bool
    {
        if (!$this->canAccess($pageId)) return false;

        if ($this->isLocked($pageId)) {
            if ($this->ownLock($pageId)) {
                Db::execute("
                    UPDATE `lock` SET
                        expire = (NOW() + INTERVAL :timeout SECOND)
                    WHERE hash = :hash",
                    [
                        'timeout' => self::TIMEOUT_SEC,
                        'hash' => $this->getPageHash($pageId)
                    ]
                );
            }
        } else {
                Db::execute("
                    INSERT INTO `lock` VALUES
                        (:hash, :pageId, :userId, :ip, (NOW() + INTERVAL :timeout SECOND))",
                    [
                        'hash' => $this->getPageHash($pageId),
                        'pageId' => $pageId,
                        'userId' => $this->getUser()->userId,
                        'ip' => Factory::instance()->getRequest()->getClientIp(),
                        'timeout' => self::TIMEOUT_SEC
                    ]
                );
        }
        return true;
    }

    public function unlock(int $pageId): bool
    {
        if (!$this->canAccess($pageId)) return true;
        return (false !== Db::execute("DELETE FROM `lock` WHERE hash = :hash",
            ['hash' => $this->getPageHash($pageId)]
        ));
    }

    /**
     * remove all locks for this user
     */
    public function clearAllLocks(): bool
    {
        return (false !== Db::execute("DELETE FROM `lock` WHERE user_id = :hash",
            ['userId' => $this->getUser()->userId]
        ));
    }

    /**
     * Can the current user
     *  - lock the requested page
     *  - access the currently locked page
     *
     * Call this to see if the user can access the lock for a page.
     *
     * NOTE: This lock system does not handle same user accounts logged into different browsers.
     *       They would both be able to use the same lock and potentially edit the same content.
     *       This system does not assume that multiple users are using the same account.
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
        return Db::queryBool("SELECT COUNT(*) FROM `lock` WHERE page_id = :pageId", compact('pageId'));
    }

    public function ownLock(int $pageId): bool
    {
        return Db::queryBool("SELECT COUNT(*) FROM `lock` WHERE hash = :hash",
            ['hash' => $this->getPageHash($pageId)]
        );
    }

    public function clearExpired(): bool
    {
        return (false !== Db::execute("DELETE FROM `lock` WHERE expire < NOW()"));
    }
}
