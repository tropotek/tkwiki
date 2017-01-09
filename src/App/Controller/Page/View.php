<?php
namespace App\Controller\Page;

use Tk\Request;
use App\Controller\Iface;

/**
 * Class Index
 *
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
     * @var \Tk\EventDispatcher\EventDispatcher
     */
    protected $dispatcher = null;

    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->dispatcher = $this->getConfig()->getEventDispatcher();
    }

    /**
     * @param Request $request
     * @param $pageUrl
     * @return \App\Page\Iface
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request, $pageUrl)
    {
        $this->wPage = \App\Db\Page::findPage($pageUrl);
        if (!$this->wPage) {
            if ($this->getUser() && $this->getUser()->getAcl()->canCreate()) {
                // Create a redirect to the page edit controller
                \Tk\Uri::create('/edit.html')->set('u', $pageUrl)->redirect();
            }
            //throw new \Tk\HttpException(404, 'Page not found');
        } else {
            if (!$this->canView()) {
                \Ts\Alert::addWarning('You do not have permission to view the page: `' . $this->wPage->title . '`');
                \Tk\Uri::create('/')->redirect();
            }

            $this->wContent = $this->wPage->getContent();

            if (!$this->wContent) {
                // May redirect to the edit page if the user has edit privileges or send alert if not.
                //throw new \Tk\Exception('Page content not found');
                \Ts\Alert::addWarning('Page content lost, please create new content.');
                \Tk\Uri::create('/edit.html')->set('pageId', $this->wPage->id)->redirect();
            }
        }
        
        return $this->show($request);
    }
    
    public function canView()
    {
        if (!$this->getUser()) {
            return ($this->wPage->permission == \App\Db\Page::PERMISSION_PUBLIC);
        }
        return $this->getUser()->getAcl()->canView($this->wPage);
    }

    /**
     * @param Request $request
     * @return \App\Page\Iface
     * @throws \Tk\Exception
     */
    public function doContentView(Request $request)
    {
        $this->wContent = \App\Db\ContentMap::create()->find($request->get('contentId'));
        if (!$this->wContent) {
            throw new \Tk\HttpException(404, 'Page not found');
        }
        $this->wPage = $this->wContent->getPage();
        if (!$this->wPage) {
            throw new \Tk\HttpException(404, 'Page not found');
        }
        return $this->show($request);
    }


    /**
     * Note: no longer a dependency on show() allows for many show methods for many 
     * controller methods (EG: doAction/showAction, doSubmit/showSubmit) in one Controller object
     * 
     * @param Request $request
     * @return \App\Page\Iface
     * @todo Look at implementing a cache for page views.
     */
    public function show(Request $request)
    {
        $template = $this->getTemplate();
        
        
        $header = new \App\Helper\PageHeader($this->wPage, $this->wContent, $this->getUser());
        $template->insertTemplate('header', $header->show());
            
        if ($this->wPage) {
            $event = new \App\Event\ContentEvent($this->wContent);
            $this->dispatcher->dispatch(\App\WikiEvents::WIKI_CONTENT_VIEW, $event);

            $template->insertHtml('content', $this->wContent->html);

            if ($this->wContent->css) {
                $template->appendCss($this->wContent->css);
            }
            if ($this->wContent->js) {
                $template->appendJs($this->wContent->js);
            }
            $template->appendJsUrl(\Tk\Uri::create($this->getConfig()->getTemplateUrl() . '/assets/prism/prism.js'));
            $template->appendCssUrl(\Tk\Uri::create($this->getConfig()->getTemplateUrl() . '/assets/prism/prism.css'));
        }
        return $this->getPage()->setPageContent($template);
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
  <div var="header" class="wiki-header"></div>
  <div var="content" class="wiki-content"></div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}