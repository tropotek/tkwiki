<?php
namespace App\Db;

use App\Db\Traits\PageTrait;
use Bs\Db\Traits\TimestampTrait;
use Bs\Db\Traits\UserTrait;
use Tk\Db\Map\Model;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Content extends Model implements \Tk\ValidInterface
{
    use TimestampTrait;
    use UserTrait;
    use PageTrait;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $pageId = 0;

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var string
     */
    public $html = '';

    /**
     * @var string
     */
    public $keywords = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $css = '';

    /**
     * @var string
     */
    public $js = '';

    /**
     * Bytes
     * @var integer
     */
    public $size = 0;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->_TimestampTrait();
    }

    /**
     * @param \App\Db\Content $src
     * @return static
     */
    static function cloneContent($src)
    {
        $dst = new static();
        $dst->setUserId(\App\Config::getInstance()->getAuthUser()->getId());
        if ($src) {
            $dst->setPageId($src->getPageId());
            $dst->setHtml($src->getHtml());
            $dst->setKeywords($src->getKeywords());
            $dst->setDescription($src->getDescription());
            $dst->setCss($src->getCss());
            $dst->setJs($src->getJs());
        }
        return $dst;
    }


    public function save()
    {
        $this->setSize(\Tk\Str::strByteSize($this->getHtml() . $this->getJs() . $this->getCss()));
        parent::save();
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     * @return Content
     */
    public function setHtml(string $html): Content
    {
        $this->html = $html;
        return $this;
    }

    /**
     * @return string
     */
    public function getKeywords(): string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     * @return Content
     */
    public function setKeywords(string $keywords): Content
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Content
     */
    public function setDescription(string $description): Content
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getCss(): string
    {
        return $this->css;
    }

    /**
     * @param string $css
     * @return Content
     */
    public function setCss(string $css): Content
    {
        $this->css = $css;
        return $this;
    }

    /**
     * @return string
     */
    public function getJs(): string
    {
        return $this->js;
    }

    /**
     * @param string $js
     * @return Content
     */
    public function setJs(string $js): Content
    {
        $this->js = $js;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return Content
     */
    public function setSize(int $size): Content
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Implement the validating rules to apply.
     *
     */
    public function validate()
    {
        $errors = array();
//        if (!$this->pageId) {  // Cannot check this here as the page id is not saved
//            $errors['pageId'] = 'Invalid page ID value.';
//        }
        if (!$this->getUserId()) {
            $errors['userId'] = 'Invalid user ID value.';
        }
        return $errors;
    }

}
