<?php
namespace App\Controller\Page;

use App\Db\Page;
use Tk\Request;
use App\Controller\Iface;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class View extends Iface
{

    /**
     * @var \App\Db\Page
     */
    protected $wPage = null;

    /**
     * @var \App\Db\Content
     */
    protected $wContent= null;


    /**
     * @param Request $request
     * @param $pageUrl
     * @throws \Exception
     */
    public function doDefault(Request $request, $pageUrl)
    {
        $this->setPageTitle('');
        $this->wPage = \App\Db\Page::findPage($pageUrl);
        if (!$this->wPage) {
            if ($this->getAuthUser() && Page::canCreate($this->getAuthUser())) {
                // Create a redirect to the page edit controller
                \Tk\Uri::create('/user/edit.html')->set('u', $pageUrl)->redirect();
            }
            //throw new \Tk\HttpException(404, 'Page not found');
        } else {
            if (!$this->canView()) {
                \Tk\Alert::addWarning('You do not have permission to view the page: `' . $this->wPage->getTitle() . '`');
                \Tk\Uri::create('/')->redirect();
            }

            $this->wContent = $this->wPage->getContent();

            if (!$this->wContent) {
                // May redirect to the edit page if the user has edit privileges or send alert if not.
                //throw new \Tk\Exception('Page content not found');
                \Tk\Alert::addWarning('Page content lost, please create new content.');
                \Tk\Uri::create('/user/edit.html')->set('pageId', $this->wPage->getId())->redirect();
            }
        }

    }

    public function canView()
    {
        if (!$this->getAuthUser()) {
            return ($this->wPage->getPermission() == \App\Db\Page::PERMISSION_PUBLIC);
        }
        return $this->wPage->canView($this->getAuthUser());
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doContentView(\Tk\Request $request)
    {
        $this->wContent = \App\Db\ContentMap::create()->find($request->get('contentId'));
        if (!$this->wContent) {
            throw new \Tk\HttpException(404, 'Page not found');
        }
        $this->wPage = $this->wContent->getPage();
        if (!$this->wPage) {
            throw new \Tk\HttpException(404, 'Page not found');
        }

    }


    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $header = new \App\Helper\PageHeader($this->wPage, $this->wContent, $this->getAuthUser());
        $template->insertTemplate('header', $header->show());


        if ($this->wPage) {

            if ($this->getConfig()->getEventDispatcher()) {
                $event = new \App\Event\ContentEvent($this->wContent);
                $this->getConfig()->getEventDispatcher()->dispatch(\App\WikiEvents::WIKI_CONTENT_VIEW, $event);
            }

            $template->insertHtml('content', $this->wContent->getHtml());

            if ($this->wContent->getCss()) {
                $template->appendCss($this->wContent->getCss());
            }
            if ($this->wContent->getJs()) {
                $template->appendJs($this->wContent->getJs());
            }

            if ($this->wContent->getKeywords()) {
                $this->getPage()->getTemplate()->appendMetaTag('keywords', $this->wContent->getKeywords(), $this->getPage()->getTemplate()->getTitleElement());
            }

            if ($this->wContent->getDescription()) {
                $this->getPage()->getTemplate()->appendMetaTag('description', $this->wContent->getDescription(), $this->getPage()->getTemplate()->getTitleElement());
            }
            $this->getPage()->getTemplate()->setTitleText($this->getPage()->getTemplate()->getTitleText() . ' - ' . $this->wPage->getTitle());


            $template->appendJsUrl(\Tk\Uri::create($this->getConfig()->getTemplateUrl() . '/app/js/prism/prism.js'));
            $template->appendCssUrl(\Tk\Uri::create($this->getConfig()->getTemplateUrl() . '/app/js/prism/prism.css'));
        }
        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="wiki-view">
  <div var="header"></div>
  <div var="content" class="wiki-content"></div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}
