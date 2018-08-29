<?php
namespace App\Listener;

use Tk\Event\Subscriber;
use Tk\Kernel\KernelEvents;

/**
 * This object helps cleanup the structure of the controller code
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PageTemplateHandler extends \Bs\Listener\PageTemplateHandler
{


    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function showPage(\Tk\Event\Event $event)
    {
        parent::showPage($event);
        $controller = $event->get('controller');
        if ($controller instanceof \Bs\Controller\Iface) {
            $page = $controller->getPage();
            if (!$page) return;
            $template = $page->getTemplate();
            /** @var \Bs\Db\User $user */
            $user = $controller->getUser();

            // WIKI Page Setup

            if ($this->getConfig()->get('site.favicon')) {
                $template->setAttr('favicon', 'href', $this->getConfig()->getDataUrl() . $this->getConfig()->get('site.favicon'));
                $template->setChoice('favicon');
            }
            if ($this->getConfig()->get('site.logo')) {
                $template->setAttr('logo', 'src', $this->getConfig()->getDataUrl() . $this->getConfig()->get('site.logo'));
                $template->setChoice('logo');
            }

            $js = <<<JS

config.widthBreakpoints = [0, 320, 481, 641, 961, 1025, 1281];

JS;
            $template->appendJs($js, array('data-jsl-priority' => -999));

            if ($user) {
                // User Menu Setup 
                $url = \Tk\Uri::create('/search.html')->set('search-terms', 'user:'.$user->hash);
                $template->setAttr('myPages', 'href', $url);
                $template->insertText('username', $user->name);
                
                if ($user->isAdmin()) {
                    $template->setChoice('admin');
                }
            }
            
            $menu = new \App\Helper\Menu($user);
            $menu->show();
            $template->replaceTemplate('wiki-menu', $menu->getTemplate());
            
            $crumbs = \App\Helper\Crumbs::getInstance(\Tk\Uri::create());
            $crumbs->show();
            $template->replaceTemplate('breadcrumb', $crumbs->getTemplate());
            


        }
    }


    /**
     * @return \App\Config|\Tk\Config
     */
    public function getConfig()
    {
        return \App\Config::getInstance();
    }

}