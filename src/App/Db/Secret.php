<?php
namespace App\Db;

use App\Db\Traits\UserTrait;
use Bs\Traits\TimestampTrait;
use Tk\DataMap\DataMap;
use Tk\DataMap\Db\Boolean;
use Tk\DataMap\Db\DateTime;
use Tk\DataMap\Db\Integer;
use Tk\DataMap\Db\Text;
use Tk\DataMap\Db\TextEncrypt;
use Tk\Db;
use Tk\Db\Filter;
use Tk\Db\Model;
use OTPHP\TOTP;

class Secret extends Model
{
    use UserTrait;
    Use TimestampTrait;

    /**
     * Page permission values
     * NOTE: Admin users have all permissions at all times
     */
    const int PERM_PRIVATE  = 9;
    const int PERM_STAFF    = 2;
    const int PERM_MEMBER   = 1;

    const array PERM_LIST = [
        self::PERM_PRIVATE  => 'Private',
        self::PERM_STAFF    => 'Staff',
        self::PERM_MEMBER   => 'Member',
    ];

    const array STAFF_PERMS = [
        self::PERM_STAFF,
        self::PERM_MEMBER,
    ];

    public int    $secretId   = 0;
    public int    $userId     = 0;
    public int    $permission = self::PERM_PRIVATE;
    public string $name       = '';
    public string $url        = '';
    public string $username   = '';
    public string $password   = '';
    public string $otp        = '';
    public bool   $publish    = true;
    public string $keys       = '';
    public string $notes      = '';
    public string $hash       = '';
    public \DateTime $modified;
    public \DateTime $created;


    public function __construct()
    {
        $this->_TimestampTrait();
    }

    public function save(): void
    {
        $map = self::getDataMap();

        $values = $map->getArray($this);
        if ($this->secretId) {
            $values['secret_id'] = $this->secretId;
            Db::update('secret', 'secret_id', $values);
        } else {
            unset($values['secret_id']);
            Db::insert('secret', $values);
            $this->secretId = Db::getLastInsertId();
        }

        $this->reload();
    }

    public function delete(): bool
    {
        return (false !== Db::delete('secret', ['secret_id' => $this->secretId]));
    }

    /**
     * create a custom data map for encrypted types
     */
    public static function getDataMap(): DataMap
    {
        $map = self::$_MAPS[self::class] ?? null;
        if (!is_null($map)) return $map;

        $map = new DataMap();
        $map->addType(new Integer('secretId', 'secret_id'))->setFlag(DataMap::PRI);
        $map->addType(new Integer('userId', 'user_id'));
        $map->addType(new Integer('permission'));
        $map->addType(new Text('name'));
        $map->addType(new TextEncrypt('url'));
        $map->addType(new TextEncrypt('username'));
        $map->addType(new TextEncrypt('password'));
        $map->addType(new TextEncrypt('otp'));
        $map->addType(new Boolean('publish'));
        $map->addType(new TextEncrypt('keys'));
        $map->addType(new TextEncrypt('notes'));
        $map->addType(new Text('hash'), DataMap::READ);
        $map->addType(new DateTime('modified'));
        $map->addType(new DateTime('created'));

        self::$_MAPS[self::class] = $map;
        return $map;
    }

    /**
     * Generate an OTP code if the OPT field is set, returns an empty string on error
     */
    public function genOtpCode(): string
    {
        $code = '';
        try {
            $otp = TOTP::create($this->otp);
            $code = $otp->now();
        } catch (\Exception $e) { }
        return $code;
    }

    /**
     * Get the page permission level as a string
     */
    public function getPermissionLabel(): string
    {
        return self::PERM_LIST[$this->permission] ?? '';
    }

    public static function find(int $id): ?self
    {
        return Db::queryOne("
            SELECT *
            FROM v_secret
            WHERE secret_id = :id",
            compact('id'),
            self::class
        );
    }

    public static function findByHash(string $hash): ?self
    {
        $hash = trim($hash);
        if (empty($hash)) return null;

        return Db::queryOne("
            SELECT *
            FROM v_secret
            WHERE hash = :hash",
            compact('hash'),
            self::class
        );
    }

    /**
     * @return array<int,Secret>
     */
    public static function findAll(): array
    {
        return Db::query("
            SELECT *
            FROM v_secret",
            null,
            self::class
        );
    }

    /**
     * @return array<int,Secret>
     */
    public static function findViewable(array|Filter $filter): array
    {
        $filter = Filter::create($filter);

        if (!empty($filter['search'])) {
            $filter['search'] = '%' . $filter['search'] . '%';
            $w  = 'LOWER(a.name) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.url) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.secret_id) LIKE LOWER(:search) OR ';
            $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['userId']) && !empty($filter['permission'] ?? '')) {
            if (!is_array($filter['userId'])) $filter['userId'] = [$filter['userId']];
            $filter->appendWhere('(a.user_id IN :userId OR ');
            if (!is_array($filter['permission'])) $filter['permission'] = [$filter['permission']];
            $filter->appendWhere('a.permission IN :permission) AND ');
        } elseif (!empty($filter['userId'])) {
            if (!is_array($filter['userId'])) $filter['userId'] = [$filter['userId']];
            $filter->appendWhere('a.user_id IN :userId AND ');
        } elseif (!empty($filter['permission'] ?? '')) {
            if (!is_array($filter['permission'])) $filter['permission'] = [$filter['permission']];
            $filter->appendWhere('a.permission IN :permission AND ');
        }

        if (!empty($filter['otp'])) {
            $filter->appendWhere("a.otp != '' AND ");
        }

        $filter->appendWhere('a.publish AND ');

        return Db::query("
            SELECT *
            FROM v_secret a
            {$filter->getSql()}",
            $filter->all(),
            self::class
        );
    }

    /**
     * @return array<int,Secret>
     */
    public static function findFiltered(array|Filter $filter): array
    {
        $filter = Filter::create($filter);
        if (!empty($filter['search'])) {
            $filter['search'] = '%' . $filter['search'] . '%';
            $w  = 'LOWER(a.name) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.url) LIKE LOWER(:search) OR ';
            $w .= 'LOWER(a.secret_id) LIKE LOWER(:search) OR ';
            $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter['secretId'] = $filter['id'];
        }
        if (!empty($filter['secretId'])) {
            if (!is_array($filter['secretId'])) $filter['secretId'] = [$filter['secretId']];
            $filter->appendWhere('a.secret_id IN :secretId AND ');
        }

        if (!empty($filter['exclude'])) {
            if (!is_array($filter['exclude'])) $filter['exclude'] = [$filter['exclude']];
            $filter->appendWhere('a.secret_id NOT IN :exclude AND ');
        }

        if (!empty($filter['userId'])) {
            if (!is_array($filter['userId'])) $filter['userId'] = [$filter['userId']];
            $filter->appendWhere('a.user_id IN :userId AND ');
        }

        if (!empty($filter['permission'])) {
            $perm = 0;
            foreach ($filter['permission'] as $p) {
                $perm |= $p;
            }
            $filter['permission'] = $perm;
            $filter->appendWhere('a.permission = :permission AND ');
        }

        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = :name AND ');
        }

        if (!empty($filter['otp'])) {
            $filter->appendWhere("a.otp != '' AND ");
        }

        if (!empty($filter['url'])) {
            $filter->appendWhere('a.url = :url AND ');
        }

        if (!empty($filter['publish'])) {
            $filter['publish'] = truefalse($filter['publish']);
            $filter->appendWhere('a.publish = :publish AND ');
        }

        return Db::query("
            SELECT *
            FROM v_secret a
            {$filter->getSql()}",
            $filter->all(),
            self::class
        );
    }


    public function validate(): array
    {
        $errors = [];

        if (!$this->userId) {
            $errors['userId'] = 'Invalid value: userId';
        }

        if (!$this->permission) {
            $errors['permission'] = 'Invalid value: permission';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if ($this->url && !filter_var($this->url, FILTER_VALIDATE_URL)) {
            $errors['url'] = 'Invalid value: url';
        }

        return $errors;
    }

    public static function canCreate(?User $user): bool
    {
        if (!$user) return false;
        if ($user->isAdmin() || $user->isStaff()) return true;
        return false;
    }

    public function canView(?User $user): bool
    {
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        if ($this->userId == $user->userId) return true;

        // Staff and users can view USER secrets
        if ($this->permission == self::PERM_MEMBER) return ($user->isMember() || $user->isStaff());

        // Staff can view STAFF secrets
        if ($this->permission == self::PERM_STAFF) return $user->isStaff();

        return false;
    }

    public function canEdit(?User $user): bool
    {
        if (!$user || $user->isMember()) return false;
        if ($user->isAdmin()) return true;
        if ($this->userId == $user->userId) return true;

        // Staff can edit MEMBER, STAFF secrets
        if (in_array($this->permission, [self::PERM_MEMBER, self::PERM_STAFF])) return $user->isStaff();

        return false;
    }
}