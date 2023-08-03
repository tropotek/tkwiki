<?php
namespace App\Db;

use Bs\Db\UserInterface;
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

    const TIMEOUT_SEC = 60*2;    // Default 2 minutes

    protected User $user;

    /**
     * @param User|UserInterface $user
     */
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

        if ($this->isLocked($pageId)) {
            if ($this->ownLock($pageId)) {
                $stm = $this->getDb()->prepare('UPDATE `lock` SET expire = (NOW() + INTERVAL :timeout SECOND) WHERE hash = :hash');
                $stm->execute([
                    'timeout' => self::TIMEOUT_SEC,
                    'hash' => $this->getPageHash($pageId)
                ]);
            }
        } else {
            $stm = $this->getDb()->prepare('INSERT INTO `lock` VALUES (:hash, :pageId, :userId, :ip, (NOW() + INTERVAL :timeout SECOND))');
            $stm->execute([
                'hash' => $this->getPageHash($pageId),
                'pageId' => $pageId,
                'userId' => $this->getUser()->getId(),
                'ip' => $this->getRequest()->getClientIp(),
                'timeout' => self::TIMEOUT_SEC
            ]);
        }
        return true;
    }

    public function unlock(int $pageId): bool
    {
        if (!$this->canAccess($pageId)) return true;
        $stm = $this->getDb()->prepare('DELETE FROM `lock` WHERE hash = :hash');
        return $stm->execute(['hash' => $this->getPageHash($pageId)]);
    }

    /**
     * remove all locks for this user
     */
    public function clearAllLocks(): bool
    {
        $stm = $this->getDb()->prepare('DELETE FROM `lock` WHERE user_id = :userId');
        return $stm->execute(['userId' => $this->getUser()->getId()]);
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
        $stm = $this->getDb()->prepare('SELECT COUNT(*) as i FROM `lock` WHERE page_id = :pageId');
        $stm->execute(compact('pageId'));
        $row = $stm->fetch();
        return ($row->i > 0);
    }

    public function ownLock(int $pageId): bool
    {
        $stm = $this->getDb()->prepare('SELECT COUNT(*) as i FROM `lock` WHERE hash = :hash');
        $stm->execute(['hash' => $this->getPageHash($pageId)]);
        $row = $stm->fetch();
        return ($row->i > 0);
    }

    public function clearExpired(): bool
    {
        $stm = $this->getDb()->prepare('DELETE FROM `lock` WHERE expire < NOW()');
        return $stm->execute();
    }
}
