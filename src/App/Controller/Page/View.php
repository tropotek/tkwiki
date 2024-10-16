<?php
namespace App\Controller\Page;

use App\Db\Content;
use App\Db\Page;
use App\Db\User;
use App\Helper\ViewToolbar;
use App\Util\Pdf;
use Bs\Mvc\ControllerPublic;
use Bs\Mvc\PageDomInterface;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tk\Alert;
use Tk\Uri;

class View extends ControllerPublic
{

    protected ?Page        $page     = null;
    protected ?Content     $content  = null;
    protected ?ViewToolbar $toolbar  = null;



    public function __construct()
    {
        $this->page = Page::findPage(Uri::create()->basename());
        // use page template if set
        if ($this->page && !empty($this->page->template)) {
            $this->setPageTemplate($this->page->template);
        }
    }

    public function doDefault(Request $request, string $pageUrl): ?Template
    {
        if ($pageUrl == Page::DEFAULT_TAG) {
            $pageUrl = Page::getHomePage()->url;
        }
        $this->page = Page::findPage($pageUrl);
        if (!$this->page) {
            if (Page::canCreate(User::getAuthUser())) {
                // Create a redirect to the page edit controller
                Uri::create('/edit')->set('u', $pageUrl)->redirect();
            } else {
                // Must be a public non-logged in user
                throw new HttpException(404, 'Page not found');
            }
        } else {
            if (!$this->page->canView(User::getAuthUser())) {
                Alert::addWarning('You do not have permission to view the page: `' . $this->page->title . '`');
                Page::getHomePage()->getUrl()->redirect();
            }
        }

        $this->page->views++;
        $this->page->save();

        $this->getPage()->setTitle($this->page->title);
        $this->content = $this->page->getContent();
        $this->toolbar = new ViewToolbar($this->page);

        if (isset($_GET['pdf'])) {
            return $this->doPdf();
        }

        return null;
    }

    /**
     * This method is used for system users viewing wiki pages
     * thus they should have edit access or this link should fail
     */
    public function doContentView(): ?Template
    {
        $this->content = Content::find(intval($_GET['contentId'] ?? 0));
        if (!$this->content) {
            throw new HttpException(404, 'page not found');
        }
        $this->page = $this->content->getPage();
        if (!$this->page) {
            throw new HttpException(404, 'page not found');
        }
        if (!$this->page->canEdit(User::getAuthUser())) {
            $this->page->getUrl()->redirect();
        }

        if (isset($_GET['pdf'])) {
            return $this->doPdf();
        }

        Alert::addInfo('You are viewing revision ' . $this->content->contentId .
            ' <a href="'.$this->page->getUrl().'">click here</a> to return to current revision');
        $this->toolbar = new ViewToolbar($this->page);

        return null;
    }

    public function doPdf(): ?Template
    {
        $rev = '-' . intval($_GET['contentId'] ?? 'unknown');

        $pdf = Pdf::create($this->content->html, $this->page->title);
        $filename = $this->page->title.$rev.'.pdf';

        if (!isset($_GET['isHtml'])) {
            $pdf->output($filename);     // comment this to see html version
        }

        return $pdf->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $template->appendTemplate('toolbar', $this->toolbar->show());

        $template->setText('title', $this->page->title);
        $template->setVisible('title', $this->page->titleVisible);

        $template->setHtml('content', $this->content->html);

        if ($this->content->css) {
            $template->appendCss($this->content->css);
        }

        if ($this->content->js) {
            $template->appendJs($this->content->js);
        }

        /** @var PageDomInterface $page */
        $page = $this->getPage();
        if ($this->content->keywords) {
            $page->getTemplate()->appendMetaTag('keywords', $this->content->keywords, $page->getTemplate()->getTitleElement());
        }

        if ($this->content->description) {
            $page->getTemplate()->appendMetaTag('description', $this->content->description, $page->getTemplate()->getTitleElement());
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="wk-content">
    <div class="sticky-top" style="" var="toolbar"></div>
    <h1 class="mb-3" choice="title"></h1>
    <div var="content"></div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

    public function getWikiPage(): ?Page
    {
        return $this->page;
    }

}