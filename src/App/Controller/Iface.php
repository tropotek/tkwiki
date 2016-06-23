<?php
namespace App\Controller;


abstract class Iface extends \Dom\Renderer\Renderer
{
    
    /**
     * @var array
     */
    protected $access = array();

    /**
     * @var string
     */
    protected $pageTitle = '';

    /**
     * @var string
     */
    protected $templatePath = '';
    
    /**
     * @var \App\Page\Iface
     */
    private $page = null;


    /**
     * @param string $pageTitle
     * @param string $access
     */
    public function __construct($pageTitle = '', $access = '')
    {
        $this->setAccess($access);
        $this->setPageTitle($pageTitle);
        $this->templatePath = $this->getConfig()->getSitePath() . $this->getConfig()->get('template.path');
    }
    
    /**
     * Get a new instance of the page to display the content in.
     *
     * @return \App\Page\Iface
     */
    public function getPage()
    {
        if (!$this->page) {
            $this->page = new \App\Page\PublicPage($this);
        }
        return $this->page;
    }

    /**
     * Set the pagefor this controller
     * 
     * @param $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     *
     * @param string $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    /**
     * Get the global config object.
     *
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return \Tk\Config::getInstance();
    }

    
    

    /**
     * Get the currently logged in user
     *
     * @return \App\Db\User
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }
    
    /**
     * Add a role(s) that can access this page
     *
     * @param string|array $role
     * @return $this
     */
    public function setAccess($role)
    {
        if (!$role) return $this;
        if (!is_array($role)) $role = array($role);
        $this->access = $role;
        return $this;
    }

    /**
     * Get the access details of this page.
     * Will return an array of role names that can be checked against the logged in user
     * 
     * @return array
     */
    public function getAccess()
    {
        return $this->access;
    }
    

}