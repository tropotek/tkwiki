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
            if ($this->getUser() && $this->getUser()->getAccess()->canCreate()) {
                // Create a redirect to the page edit controller
                \Tk\Uri::create('/edit.html')->set('u', $pageUrl)->redirect();
            }
            throw new \Tk\HttpException(404, 'Page not found');
        }
        
        $this->wContent = $this->wPage->getContent();
        if (!$this->wContent) {
            // May redirect to the edit page if the user has edit privileges or send alert if not.
            //throw new \Tk\Exception('Page content not found');
            \App\Alert::addWarning('Page content lost, please create new content.');
            \Tk\Uri::create('/edit.html')->set('pageId', $this->wPage->id)->redirect();
        }
        
        return $this->show($request);
    }


    /**
     * Note: no longer a dependency on show() allows for many show methods for many 
     * controller methods (EG: doAction/showAction, doSubmit/showSubmit) in one Controller object
     * 
     * @param Request $request
     * @return \App\Page\PublicPage
     * @todo Look at implementing a cache for page views.
     */
    public function show(Request $request)
    {
        $template = $this->getTemplate();
        
        $header = new \App\Helper\PageHeader($this->wPage, $this->getUser());
        $template->insertTemplate('header', $header->show());
        
        $event = new \App\Event\ContentEvent($this->wContent, $this, $request);
        $this->dispatcher->dispatch(\App\Events::WIKI_CONTENT_VIEW, $event);

        $template->insertHtml('content', $this->wContent->html);
        
        if ($this->wContent->css) {
            $template->appendCss($this->wContent->css);
        }
        if ($this->wContent->js) {
            $template->appendJs($this->wContent->js);
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