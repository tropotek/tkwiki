<?php
namespace App\Page;

use Tk\Request;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PublicPage extends Iface
{

    /**
     * AdminPage constructor.
     *
     * @param \App\Controller\Iface $controller
     * @param string $templateFile
     */
    public function __construct(\App\Controller\Iface $controller, $templateFile = '')
    {
        parent::__construct($controller, $templateFile);
    }

    /**
     * Get the logged in user.
     * 
     * @return \App\Db\User
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

    /**
     * 
     */
    public function show()
    {
        $this->initPage();
        
        if ($this->getUser()) {
            
            // todo, show choices `admin` `moderator` `user` .... 
            
        }
        
        
        
    }
    
}