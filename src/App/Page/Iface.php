<?php
namespace App\Page;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Iface extends \Tk\Controller\Page
{


    /**
     * Set the page heading, should be set from main controller
     *
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        if (\Tk\AlertCollection::hasMessages()) {
            $template->insertTemplate('alerts', \Tk\AlertCollection::getInstance()->show());
            $template->setChoice('alerts');
        }

        if ($this->getUser()) {
            $template->setChoice('logout');
            $template->insertText('username', $this->getUser()->name);
            $template->setAttr('user-home', 'href', $this->getUser()->getHomeUrl());
            $template->setAttr('userUrl', 'href', $this->getUser()->getHomeUrl());
        } else {
            $template->setChoice('login');
        }

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

        return $template;
    }

    /**
     * Get the currently logged in user
     *
     * @return \Bs\Db\User
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

    /**
     * Get the global config object.
     *
     * @return \App\Config
     */
    public function getConfig()
    {
        return \App\Config::getInstance();
    }

}
