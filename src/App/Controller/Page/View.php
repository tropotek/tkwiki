<?php
namespace App\Controller\Page;

use App\Db\Content;
use App\Db\ContentMap;
use App\Db\Page;
use App\Db\User;
use App\Helper\ViewToolbar;
use App\Util\Pdf;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tk\Alert;
use Tk\Uri;

class View extends PageController
{

    protected ?Page $wPage = null;

    protected ?Content $wContent = null;

    protected ?ViewToolbar $toolbar = null;


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
    }

    public function doDefault(Request $request, string $pageUrl)
    {
        if ($pageUrl == Page::DEFAULT_TAG) {
            $pageUrl = $this->getRegistry()->get('wiki.page.default');
        }
        $this->wPage = Page::findPage($pageUrl);
        if (!$this->wPage) {
            if (Page::canCreate($this->getFactory()->getAuthUser())) {
                // Create a redirect to the page edit controller
                Uri::create('/edit')->set('u', $pageUrl)->redirect();
            }
        } else {
            if (!$this->wPage->canView($this->getFactory()->getAuthUser())) {
                Alert::addWarning('You do not have permission to view the page: `' . $this->wPage->getTitle() . '`');
                Uri::create('/')->redirect();
            }
        }

        $this->getPage()->setTitle($this->wPage->getTitle());
        $this->wContent = $this->wPage->getContent();
        $this->toolbar = new ViewToolbar($this->wPage);
        //$this->getFactory()->getCrumbs()->addCrumb($this->wPage->getUrl(), $this->wPage->getTitle());

        // TODO: Note this should never happen (if it does then we need to look at the forign key in the DB)
//        if (!$this->wContent) {
//            // May redirect to the edit page if the user has edit privileges or send alert if not.
//            \Tk\Alert::addWarning('Page content lost, please create new content.');
//            \Tk\Uri::create('/edit')->set('id', $this->wPage->getId())->redirect();
//        }


        if ($request->query->has('pdf')) {
            return $this->doPdf($request);
        }

        return $this->getPage();
    }

    public function doContentView(Request $request)
    {
        $this->wContent = ContentMap::create()->find($request->get('contentId'));
        if (!$this->wContent) {
            throw new HttpException(404, 'Page not found');
        }
        $this->wPage = $this->wContent->getPage();
        if (!$this->wPage) {
            throw new HttpException(404, 'Page not found');
        }

        if ($request->query->has('pdf')) {
            return $this->doPdf($request);
        }
    }

    public function doPdf(Request $request)
    {
        $rev = '';
        if ($request->query->get('contentId')) {
            $rev = '-' . $request->query->get('contentId');
        }

        $pdf = Pdf::create($this->wContent->getHtml(), $this->wPage->getTitle());
        $filename = $this->wPage->getTitle().$rev.'.pdf';
        if (!$request->query->has('isHtml'))
            $pdf->output($filename);     // comment this to see html version

        return $pdf->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        // TODO:
//        $header = new \App\Helper\PageHeader($this->wPage, $this->wContent, $this->getAuthUser());
//        $template->insertTemplate('header', $header->show());
        $template->appendTemplate('toolbar', $this->toolbar->show());

        if ($this->getFactory()->getEventDispatcher()) {
            $event = new \App\Event\ContentEvent($this->wContent);
            $this->getFactory()->getEventDispatcher()->dispatch($event, \App\WikiEvents::WIKI_CONTENT_VIEW);
        }

        $template->setText('title', $this->wPage->getTitle());
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
        $title = trim($this->getPage()->getTemplate()->getTitleText());
        if ($title) {
            $title .= ' - ';
        }
        $this->getPage()->getTemplate()->setTitleText($title . $this->wPage->getTitle());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
    <div var="toolbar"></div>
    <h1 var="title"></h1>
    <div var="content"></div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}