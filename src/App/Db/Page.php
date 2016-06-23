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
class Page extends Model
{
    /**
     * The default tag string used for routes
     * This actual default page url should be looked up in the config
     * when this tag is returned from the router
     */
    const DEFAULT_TAG = '__default';
    
    const TYPE_PAGE = 'page';
    const TYPE_NAV = 'nav';
    
    
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var string
     */
    public $type = self::TYPE_PAGE;

    /**
     * @var string
     */
    public $template = 'main.xtpl';

    /**
     * @var string
     */
    public $title = '';

    /**
     * @var string
     */
    public $url = '';

    /**
     * @var integer
     */
    public $views = 0;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;

    /**
     * @var \App\Db\Content
     */
    private $content = null;

    /**
     * @var \App\Db\User
     */
    private $user = null;
    

    /**
     * User constructor.
     * 
     */
    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->created = new \DateTime();
    }
    
    public function save()
    {
        // TODO: make sure the url is unique
        // Do not error on the url it needs to be automatic
        
        return parent::save();
    }

    /**
     * 
     * @param string $url
     * @return Page
     */
    static public function findPage($url)
    {
        if ($url == self::DEFAULT_TAG) {
            $url = \App\Factory::getConfig()->get('wiki.page.default');
        }
        $page = self::getMapper()->findByUrl($url);
        return $page;
    }

    /**
     * 
     * @return \App\Db\Content
     */
    public function getContent()
    {
        if (!$this->content) {
            $this->content = \App\Db\Content::getMapper()->findByPageId($this->id, \Tk\Db\Tool::create('created', 1))->current();
        }
        return $this->content;
    }

    /**
     *
     * @return User|null
     */
    public function getUser()
    {
        if (!$this->user) {
            $this->user = \App\Db\User::getMapper()->find($this->userId);
        }
        return $this->user;
    }


}

class PageValidator extends \App\Helper\Validator
{

    /**
     * Implement the validating rules to apply.
     *
     */
    protected function validate()
    {
        /** @var Page $obj */
        $obj = $this->getObject();

        if (!$obj->userId) {
            $this->addError('userId', 'Invalid user ID value.');
        }
        if (!$obj->title) {
            $this->addError('title', 'Please enter a title for your page');
        }
        
        $comp = \App\Db\Page::getMapper()->findByUrl($obj->url);
        if ($comp && $comp->id != $obj->id) {
            $this->addError('url', 'This url already exists, try again.');
        }
        if (!$obj->url) {
            $this->addError('url', 'Please enter a URL for your page');
        }
        
        // TODO: ????
        
        
    }
}