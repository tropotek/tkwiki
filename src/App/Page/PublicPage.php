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
     * 
     */
    public function show()
    {
        $this->initPage();
        $template = $this->getTemplate();
        
         
        if ($this->getUser()) {
            // User Menu Setup 
            $url = \Tk\Uri::create('/search.html')->set('mode', 'user:'.$this->getUser()->id);
            $template->setAttr('myPages', 'href', $url);
        }
        
        
        
    }
    
}