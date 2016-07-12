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
     * @var string
     */
    protected $templateFile = '';


    /**
     * Iface constructor.
     *
     * @param \App\Controller\Iface $controller
     * @param string $templateFile
     */
    public function __construct(\App\Controller\Iface $controller, $templateFile = '')
    {
        $this->controller = $controller;
        if (!$templateFile) {
            $templateFile = $this->getTemplatePath() . '/main.xtpl';
        }
        $this->templateFile = $templateFile;
        
        // TODO: Check this call should be here, or called externally????
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


        if ($this->getConfig()->get('site.title')) {
            $template->setAttr('siteName', 'title', $this->getConfig()->get('site.title'));
            $template->setTitleText(trim($template->getTitleText() . ' - ' . $this->getConfig()->get('site.title'), '- '));
        }
        if ($this->getController()->getPageTitle()) {
            $template->setTitleText($this->getController()->getPageTitle() . ' - ' . $template->getTitleText());
            $template->insertText('pageHeading', $this->getController()->getPageTitle());
            $template->setChoice('pageHeading');
        }
        if ($this->getConfig()->isDebug()) {
            $template->setTitleText(trim('DEBUG: ' . $template->getTitleText(), '- '));
        }


        if ($this->controller->getUser()) {
            $template->setChoice('logout');
        } else {
            $template->setChoice('login');
        }
        
        $noticeTpl = \App\Alert::getInstance()->show()->getTemplate();
        $template->insertTemplate('alerts', $noticeTpl)->setChoice('alerts');
        
        if ($this->getConfig()->get('site.user.registration')) {
            $template->setChoice('register');
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
        
        
        return $this;
    }

    /**
     * Set the page Content
     *
     * @param string|\Dom\Template|\Dom\Renderer\RendererInterface|\DOMDocument $content
     * @return PublicPage
     */
    public function setPageContent($content)
    {
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
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    /**
     * @param string $templateFile
     */
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = $templateFile;
    }
    
    /**
     * Get the template path location
     * 
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->controller->getTemplatePath();
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
        return \Dom\Loader::loadFile($this->getTemplateFile());
    }

}
