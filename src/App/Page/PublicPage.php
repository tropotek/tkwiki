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
            $url = \Tk\Uri::create('/search.html')->set('search-terms', 'user:'.$this->getUser()->id);
            $template->setAttr('myPages', 'href', $url);
        }
        $siteUrl = $this->getConfig()->getSiteUrl();
        $dataUrl = $this->getConfig()->getDataUrl();
        $js = <<<JS

var config = {
  siteUrl : '$siteUrl',
  dataUrl : '$dataUrl'
};
JS;
        $template->appendJs($js, ['data-jsl-priority' => -1000]);
        
        
        $menu = new \App\Helper\Menu($this->getUser());
        $menu->show();
        $template->insertTemplate('wiki-menu', $menu->getTemplate());
        
    }
    
}