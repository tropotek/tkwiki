<?php
namespace App\Page;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Iface extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{

    /**
     * @var \App\Controller\Iface
     */
    protected $controller = null;


    /**
     * Iface constructor.
     *
     * @param \App\Controller\Iface $controller
     * @param string $templateFile
     */
    public function __construct(\App\Controller\Iface $controller)
    {
        $this->controller = $controller;
        $this->show();
    }

    /**
     * Set the page heading, should be set from main controller
     *
     * @return $this
     * @throws \Dom\Exception
     */
    protected function initPage()
    {
        /** @var \Dom\Template $template */
        $template = $this->getTemplate();

        if ($this->getConfig()->get('site.meta.keywords')) {
            $template->appendMetaTag('keywords', $this->getConfig()->get('site.meta.keywords'), $template->getTitleElement());
        }
        if ($this->getConfig()->get('site.meta.description')) {
            $template->appendMetaTag('description', $this->getConfig()->get('site.meta.description'), $template->getTitleElement());
        }
        
        
        if ($this->getConfig()->get('site.favicon')) {
            $template->setAttr('favicon', 'href', $this->getConfig()->getDataUrl() . $this->getConfig()->get('site.favicon'));
        }
        if ($this->getConfig()->get('site.logo')) {
            $template->setAttr('logo', 'src', $this->getConfig()->getDataUrl() . $this->getConfig()->get('site.logo'));
        }

        if ($this->getConfig()->get('system.authors'))
            $template->appendMetaTag('tk-author', $this->getConfig()->get('system.authors'), $template->getTitleElement());
        if ($this->getConfig()->get('system.project'))
            $template->appendMetaTag('tk-project', $this->getConfig()->get('system.project'), $template->getTitleElement());
        if ($this->getConfig()->get('system.version'))
            $template->appendMetaTag('tk-version', $this->getConfig()->get('system.version'), $template->getTitleElement());


        if ($this->getConfig()->get('site.title')) {
            $template->setAttr('siteName', 'title', $this->getConfig()->get('site.title'));
            $template->setTitleText(trim($template->getTitleText() . ' - ' . $this->getConfig()->get('site.title'), '- '));
        }

        if ($this->getUser()) {
            $template->setChoice('logout');
        } else {
            $template->setChoice('login');
        }
        
        $noticeTpl = \Ts\AlertCollection::getInstance()->show()->getTemplate();
        $template->insertTemplate('alerts', $noticeTpl)->setChoice('alerts');
        
        if ($this->getConfig()->get('site.user.registration')) {
            $template->setChoice('register');
        }

        $siteUrl = $this->getConfig()->getSiteUrl();
        $dataUrl = $this->getConfig()->getDataUrl();
        $templateUrl = $this->getConfig()->getTemplateUrl();

        $js = <<<JS
var config = {
  siteUrl : '$siteUrl',
  dataUrl : '$dataUrl',
  templateUrl: '$templateUrl',
  widthBreakpoints: [0, 320, 481, 641, 961, 1025, 1281]
};
JS;
        $template->appendJs($js, ['data-jsl-priority' => -1000]);

        if ($this->getConfig()->get('site.global.js')) {
            $template->appendJs($this->getConfig()->get('site.global.js'));
        }
        if ($this->getConfig()->get('site.global.css')) {
            $template->appendCss($this->getConfig()->get('site.global.css'));
        }

        $event = new \Tk\EventDispatcher\Event();
        $event->set('template', $template);
        $event->set('page', $this);
        $event->set('controller', $this->getController());
        \App\Factory::getEventDispatcher()->dispatch(\App\AppEvents::PAGE_INIT, $event);

        
        return $this;
    }

    /**
     *
     */
    protected function renderPageTitle()
    {
        $template = $this->getTemplate();
        if ($this->getController()->getPageTitle()) {
            $template->setTitleText(trim($this->getController()->getPageTitle() . ' - ' . $template->getTitleText(), '- '));
            $template->insertText('pageHeading', $this->getController()->getPageTitle());
            $template->setChoice('pageHeading');
        }
        if ($this->getConfig()->isDebug()) {
            $template->setTitleText(trim('DEBUG: ' . $template->getTitleText(), '- '));
        }
    }

    /**
     * Set the page Content
     *
     * @param string|\Dom\Template|\Dom\Renderer\RendererInterface|\DOMDocument $content
     * @return $this
     */
    public function setPageContent($content)
    {
        // Allow people to hook into the controller result.
        $event = new \Tk\EventDispatcher\Event();
        $event->set('controllerResult', $content);
        $event->set('controller', $this->getController());
        \App\Factory::getEventDispatcher()->dispatch(\App\AppEvents::SHOW, $event);

        $this->renderPageTitle();
        if (!$content) return $this;
        if ($content instanceof \Dom\Template) {
            $this->getTemplate()->appendTemplate('content', $content);
        } else if ($content instanceof \Dom\Renderer\RendererInterface) {
            $this->getTemplate()->appendTemplate('content', $content->getTemplate());
        } else if ($content instanceof \DOMDocument) {
            $this->getTemplate()->insertDoc('content', $content);
        } else if (is_string($content)) {
            $this->template->insertHtml('content', $content);
        }
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->getConfig()->getTemplatePath();
    }

    /**
     * @return \App\Controller\Iface
     */
    public function getController()
    {
        return $this->controller;
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
     * Get the global config object.
     *
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return \Tk\Config::getInstance();
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $tplFile =  $this->getConfig()->getTemplatePath().'/main.xtpl';
        return \Dom\Loader::loadFile($tplFile);
    }

}
