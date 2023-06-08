<?php
namespace App\Db;

use App\Db\Traits\PageTrait;
use App\Db\Traits\UserTrait;
use App\Factory;
use Bs\Db\Traits\CreatedTrait;
use Tk\Db\Mapper\Model;

class Content extends Model
{
    use CreatedTrait;
    use UserTrait;
    use PageTrait;

    public int $id = 0;

    public int $pageId = 0;

    public int $userId = 0;

    public string $html = '';

    public string $keywords = '';

    public string $description = '';

    public string $css = '';

    public string $js = '';

    public \DateTime $created;



    public function __construct()
    {
        $this->_CreatedTrait();
        $this->userId = Factory::instance()->getAuthUser()?->getId() ?? 0;
    }

    public static function cloneContent(Content $src): Content
    {
        $dst = new static();
        $dst->userId = Factory::instance()->getAuthUser()?->getId() ?? 0;

        $dst->pageId = $src->pageId;
        $dst->html = $src->html;
        $dst->keywords = $src->keywords;
        $dst->description = $src->description;
        $dst->css = $src->css;
        $dst->js = $src->js;

        return $dst;
    }

    /**
     * compare this content to the supplied content and return true if they differ
     * Use this to check if a new content should be saved on edit
     */
    public function diff(Content $content): bool
    {
        if ($this->getHtml() != $content->getHtml()) {
            return true;
        }
        if ($this->getKeywords() != $content->getKeywords()) {
            return true;
        }
        if ($this->getDescription() != $content->getDescription()) {
            return true;
        }
        if ($this->getCss() != $content->getCss()) {
            return true;
        }
        if ($this->getJs() != $content->getJs()) {
            return true;
        }
        
        return false;
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


    public function validate(): array
    {
        $errors = [];

        if (!$this->getUserId()) {
            $errors['userId'] = 'Invalid value: userId';
        }

        if (!$this->getPageId()) {
            $errors['pageId'] = 'Invalid value: pageId';
        }

        return $errors;
    }

}