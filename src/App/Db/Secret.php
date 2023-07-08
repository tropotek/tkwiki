<?php
namespace App\Db;

use Bs\Db\Traits\UserTrait;
use Bs\Db\Traits\TimestampTrait;
use OTPHP\TOTP;
use Tk\Db\Mapper\Model;

class Secret extends Model
{
    use UserTrait;
    Use TimestampTrait;

    /**
     * Page permission values
     * NOTE: Admin users have all permissions at all times
     */
    const PERM_PRIVATE            = 9;
    const PERM_STAFF              = 2;
    const PERM_USER               = 1;

    const PERM_LIST = [
        self::PERM_PRIVATE   => 'Private',
        self::PERM_STAFF     => 'Staff',
        self::PERM_USER      => 'User',
    ];

    const PERM_HELP = [
        self::PERM_PRIVATE   => 'VIEW: author, EDIT: author, DELETE: author',
        self::PERM_STAFF     => 'VIEW: staff users, EDIT: staff editors, DELETE: staff editors',
        self::PERM_USER      => 'VIEW: registered users, EDIT: staff, DELETE: staff',
    ];

    protected int $secretId = 0;

    protected int $userId = 0;

    protected int $permission = self::PERM_PRIVATE;

    protected string $name = '';

    protected string $url = '';

    protected string $username = '';

    protected string $password = '';

    protected string $otp = '';

    protected string $keys = '';

    protected string $notes = '';

    protected \DateTime $modified;

    protected \DateTime $created;



    public function __construct()
    {
        $this->_TimestampTrait();
    }

    /**
     * Generate an OTP code if the OPT field is set, returns an empty string on error
     */
    public function genOtpCode(): string
    {
        $code = '';
        try {
            $otp = TOTP::create($this->getOtp());
            $code = $otp->now();
        } catch (\Exception $e) { }
        return $code;
    }

    public function getSecretId(): int
    {
        return $this->secretId;
    }

    public function setSecretId(int $secretId): Secret
    {
        $this->secretId = $secretId;
        return $this;
    }


    public function getPermission(): int
    {
        return $this->permission;
    }

    public function setPermission(int $permission): Secret
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * Get the page permission level as a string
     */
    public function getPermissionLabel(): string
    {
        return self::PERM_LIST[$this->getPermission()] ?? '';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Secret
    {
        $this->name = $name;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Secret
    {
        $this->url = $url;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): Secret
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): Secret
    {
        $this->password = $password;
        return $this;
    }

    public function getOtp(): string
    {
        return $this->otp;
    }

    public function setOtp(string $otp): Secret
    {
        $this->otp = $otp;
        return $this;
    }

    public function getKeys(): string
    {
        return $this->keys;
    }

    public function setKeys(string $keys): Secret
    {
        $this->keys = $keys;
        return $this;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): Secret
    {
        $this->notes = $notes;
        return $this;
    }


    public function validate(): array
    {
        $errors = [];

        if (!$this->getUserId()) {
            $errors['userId'] = 'Invalid value: userId';
        }

        if (!$this->getPermission()) {
            $errors['permission'] = 'Invalid value: permission';
        }

        if (!$this->getName()) {
            $errors['name'] = 'Invalid value: name';
        }

        if ($this->getUrl() && !filter_var($this->getUrl(), FILTER_VALIDATE_URL)) {
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
        if ($this->getUserId() == $user->getUserId()) return true;

        // Staff and users can view USER secrets
        if ($this->getPermission() == self::PERM_USER) {
            return ($user->isMember() || $user->isStaff());
        }

        // Staff can view STAFF secrets
        if ($this->getPermission() == self::PERM_STAFF) {
            return $user->isStaff();
        }

        return false;
    }

    public function canEdit(?User $user): bool
    {
        if (!$user) return false;
        if ($user->isMember()) return false;
        if ($user->isAdmin()) return true;
        if ($this->getUserId() == $user->getUserId()) return true;

        // Allow any staff to edit public or user secrets
        if ($this->getPermission() == self::PERM_USER) {
            return $user->isStaff();
        }

        // Only Editors can edit staff secrets
        if ($this->getPermission() == self::PERM_STAFF) {
            return $user->hasPermission(User::PERM_EDITOR);
        }

        return false;
    }

    public function canDelete(?User $user): bool
    {
        if (!$user) return false;
        if ($user->isMember()) return false;
        if ($user->isAdmin()) return true;
        if ($this->getUserId() == $user->getUserId()) return true;

        // Allow any staff to delete public or user secrets
        if ($this->getPermission() == self::PERM_USER) {
            return $user->isStaff();
        }

        // Only Editors can delete staff secrets
        if ($this->getPermission() == self::PERM_STAFF) {
            return $user->hasPermission(User::PERM_EDITOR);
        }

        return false;
    }
}