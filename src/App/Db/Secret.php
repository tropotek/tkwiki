<?php
namespace App\Db;

use App\Db\Traits\UserTrait;
use Bs\Db\Traits\TimestampTrait;
use OTPHP\TOTP;
use Tk\Db\Mapper\Model;

class Secret extends Model
{
    use UserTrait;
    Use TimestampTrait;

    public int $id = 0;

    public int $userId = 0;

    public int $permission = Page::PERM_PRIVATE;

    public string $name = '';

    public string $url = '';

    public string $username = '';

    public string $password = '';

    public string $otp = '';

    public string $keys = '';

    public string $notes = '';

    public \DateTime $modified;

    public \DateTime $created;



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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): Secret
    {
        $this->userId = $userId;
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

        if (!$this->userId) {
            $errors['userId'] = 'Invalid value: userId';
        }

        if (!$this->permission) {
            $errors['permission'] = 'Invalid value: permission';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        return $errors;
    }

}