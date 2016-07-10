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
            $url = \Tk\Uri::create('/search.html')->set('search-terms', 'user:'.$this->getUser()->hash);
            $template->setAttr('myPages', 'href', $url);
            $template->insertText('username', $this->getUser()->name);
            
            if ($this->getUser()->getAccess()->isAdmin()) {
                $template->setChoice('admin');
            }
        }

        if ($this->getConfig()->get('site.title')) {
            $template->setAttr('siteName', 'title', $this->getConfig()->get('site.title'));
            $template->setTitleText(trim($template->getTitleText() . ' - ' . $this->getConfig()->get('site.title'), '- '));
        }
        if ($this->getConfig()->isDebug()) {
            $template->setTitleText(trim('DEBUG: ' . $this->getConfig()->get('site.title'), '- '));
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
        $template->replaceTemplate('wiki-menu', $menu->getTemplate());
        
        $crumbs = \App\Helper\Crumbs::getInstance(\Tk\Uri::create());
        $crumbs->show();
        $template->replaceTemplate('wiki-crumbs', $crumbs->getTemplate());
        
    }
    
}