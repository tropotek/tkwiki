<?php
namespace App\Db;

use Tk\Db\Map\Model;


/**
 * Class User
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Content extends Model implements \Tk\ValidInterface
{

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
     * @var \Bs\Db\User
     */
    private $user = null;

    /**
     * @var \App\Db\Page
     */
    private $page = null;


    /**
     * User constructor.
     *
     */
    public function __construct()
    {
        $this->modified = \Tk\Date::create();
        $this->created = \Tk\Date::create();
    }

    /**
     * @param \App\Db\Content $src
     * @return static
     */
    static function cloneContent($src)
    {
        $dst = new static();
        $dst->userId = \App\Config::getInstance()->getAuthUser()->id;
        if ($src) {
            $dst->pageId = $src->pageId;
            $dst->html = $src->html;
            $dst->keywords = $src->keywords;
            $dst->description = $src->description;
            $dst->css = $src->css;
            $dst->js = $src->js;
        }
        return $dst;
    }

    /**
     *
     * @return Page|null
     * @throws \Exception
     */
    public function getPage()
    {
        if (!$this->page) {
            $this->page = \App\Db\PageMap::create()->find($this->pageId);
        }
        return $this->page;
    }

    /**
     *
     * @return \Bs\Db\User|null
     * @throws \Exception
     */
    public function getUser()
    {
        if (!$this->user) {
            $this->user = \Bs\Db\UserMap::create()->find($this->userId);
        }
        return $this->user;
    }

    public function save()
    {
        // TODO: calculate content size...
        $this->size = \Tk\Str::strByteSize($this->html.$this->js.$this->css);

        parent::save();
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
        if (!$this->userId) {
            $errors['userId'] = 'Invalid user ID value.';
        }
        return $errors;
    }

}
