<?php
namespace App\Db;

use Tk\Db\Mapper\Model;

class Content extends Model
{

    public int $id = 0;

    public int $pageId = 0;

    public int $userId = 0;

    public string $html = '';

    public string $keywords = '';

    public string $description = '';

    public string $css = '';

    public string $js = '';

    public \DateTime $modified;

    public \DateTime $created;



    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->created = new \DateTime();

    }
    
    public function setPageId(int $pageId): Content
    {
        $this->pageId = $pageId;
        return $this;
    }

    public function getPageId(): int
    {
        return $this->pageId;
    }

    public function setUserId(int $userId): Content
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setHtml(string $html): Content
    {
        $this->html = $html;
        return $this;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function setKeywords(string $keywords): Content
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function setDescription(string $description): Content
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setCss(string $css): Content
    {
        $this->css = $css;
        return $this;
    }

    public function getCss(): string
    {
        return $this->css;
    }

    public function setJs(string $js): Content
    {
        $this->js = $js;
        return $this;
    }

    public function getJs(): string
    {
        return $this->js;
    }

    public function setModified(\DateTime $modified): Content
    {
        $this->modified = $modified;
        return $this;
    }

    public function getModified(): \DateTime
    {
        return $this->modified;
    }

    public function setCreated(\DateTime $created): Content
    {
        $this->created = $created;
        return $this;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }


    public function validate(): array
    {
        $errors = [];

        if (!$this->pageId) {
            $errors['pageId'] = 'Invalid value: pageId';
        }

        if (!$this->html) {
            $errors['html'] = 'Invalid value: html';
        }

        if (!$this->keywords) {
            $errors['keywords'] = 'Invalid value: keywords';
        }

        if (!$this->description) {
            $errors['description'] = 'Invalid value: description';
        }

        return $errors;
    }

}