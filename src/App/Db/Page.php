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
class Page extends Model implements \Tk\ValidInterface
{
    /**
     * The default tag string used for routes
     * This actual default page url should be looked up in the config
     * when this tag is returned from the router
     */
    const DEFAULT_TAG = '__default';
    
    /**
     * All users can read page
     * Only registered users with edit roles can edit
     */
    const PERMISSION_PUBLIC = 0;
    /**
     * All registered users can read page
     * Only registered users with edit roles can edit 
     */
    const PERMISSION_PROTECTED = 1;
    /**
     * Only the author can edit and read the page
     */
    const PERMISSION_PRIVATE = 2;
    
    /**
     * This type is a standard content page
     */
    const TYPE_PAGE = 'page';
    /**
     * This type means that the page is to be used with the menu/nav only
     */
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
    public $template = '';

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
     * @var integer
     */
    public $permission = 0;

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
        $this->modified = \Tk\Date::create();
        $this->created = \Tk\Date::create();
    }

    /**
     * create a unique url by comparing to the 
     * existing urls and adding to a tail count if duplicated exist.
     * 
     * EG: 
     *   Home
     *   Home_1
     *   Home_2
     *   ...
     * 
     * @param $title
     * @return mixed|string
     */
    static public function makeUrl($title)
    {
        $url = preg_replace('/[^a-z0-9_-]/i', '_', $title);
        do {
            $comp = \App\Db\PageMap::create()->findByUrl($url);
            if ($comp) {
                if (preg_match('/(.+)(_([0-9]+))$/', $url, $regs)) {
                    $url = $regs[1] . '_' . ($regs[3]+1);
                } else {
                    $url = $url.'_1';
                }
            }
        } while($comp);
        vd($url);
        return $url;
    }
    
    public function save()
    {
        if (!$this->url && !$this->id) {
            $this->url = $this->makeUrl($this->title);
        }
        parent::save();
    }

    /**
     * @return int
     * @throws \Tk\Db\Exception
     */
    public function delete()
    {
        // remove page any locks (this could be redundant and left up to the expired cleanup)
        \App\Config::getInstance()->getLockMap()->unlock($this->id);
        
        // delete all page links referred to by this page.
        $this->getMapper()->deleteLinkByPageId($this->id);
        
        // Remove all content
        $contentList = \App\Db\ContentMap::create()->findByPageId($this->id);
        foreach ($contentList as $c) {
            $c->delete();
        }
        return parent::delete();
    }

    /**
     * This returns a \Tk\Uri object pointing to the page.
     * 
     * @return \Tk\Uri
     */
    public function getPageUrl()
    {
        return \Tk\Uri::create('/'.trim($this->url, '/'));
    }
    
    /**
     * 
     * @param string $url
     * @return Page
     */
    static public function findPage($url)
    {
        if ($url == self::DEFAULT_TAG) {
            $url = \App\Config::getInstance()->get('wiki.page.default');
        }
        $page = self::getMapper()->findByUrl($url);
        return $page;
    }

    /**
     * @return string
     */
    static public function getHomeUrl()
    {
        return \App\Config::getInstance()->get('wiki.page.default');
    }

    /**
     * 
     * @return \App\Db\Content
     */
    public function getContent()
    {
        if (!$this->content) {
            $this->content = \App\Db\ContentMap::create()->findByPageId($this->id, \Tk\Db\Tool::create('created DESC', 1))->current();
        }
        return $this->content;
    }


    /**
     * Get the page permission level as a string
     * @return string
     */
    public function getPermissionLabel()
    {
        switch($this->permission) {
            case self::PERMISSION_PRIVATE;
                return 'private';
            case self::PERMISSION_PROTECTED;
                return 'protected';
            
        }
        return 'public';
    }
    

    /**
     *
     * @return User|null
     */
    public function getUser()
    {
        if (!$this->user) {
            $this->user = \App\Db\UserMap::create()->find($this->userId);
        }
        return $this->user;
    }

    /**
     * Validate this object's current state and return an array
     * with error messages. This will be useful for validating
     * objects for use within forms.
     *
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->userId) {
            $errors['userId'] = 'Invalid user ID value';
        }
        if (!$this->title) {
            $errors['title'] = 'Please enter a title for your page';
        }
        if($this->id) {
            $comp = \App\Db\PageMap::create()->findByUrl($this->url);
            if ($comp && $comp->id != $this->id) {
                $errors['url'] = 'This url already exists, try again';
            }
        }
        return $errors;
    }
}
