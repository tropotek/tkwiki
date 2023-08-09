<?php
namespace App\Controller\Page;

use App\Db\Content;
use App\Db\ContentMap;
use App\Db\Page;
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


    public function doDefault(Request $request, string $pageUrl): Template|\App\Page|\Dom\Mvc\Page|null
    {
        if ($pageUrl == Page::DEFAULT_TAG) {
            $pageUrl = $this->getRegistry()->get('wiki.page.default');
        }
        $this->wPage = Page::findPage($pageUrl);
        if (!$this->wPage) {
            if (Page::canCreate($this->getFactory()->getAuthUser())) {
                // Create a redirect to the page edit controller
                Uri::create('/edit')->set('u', $pageUrl)->redirect();
            } else {
                // Must be a public non-logged in user
                throw new HttpException(404, 'Page not found');
            }
        } else {
            if (!$this->wPage->canView($this->getFactory()->getAuthUser())) {
                Alert::addWarning('You do not have permission to view the page: `' . $this->wPage->getTitle() . '`');
                Uri::create(Page::getHomeUrl())->redirect();
            }
        }

        if ($this->wPage->getTemplate()) {
            $tplPath = $this->getSystem()->makePath(sprintf('/html/%s.html', $this->wPage->getTemplate()));
            $this->setPage($this->getFactory()->createPage($tplPath));
        }

        $this->getPage()->setTitle($this->wPage->getTitle());
        $this->wContent = $this->wPage->getContent();
        $this->toolbar = new ViewToolbar($this->wPage);

        if ($request->query->has('pdf')) {
            return $this->doPdf($request);
        }

        return $this->getPage();
    }

    /**
     * This method is used for system users viewing wiki pages
     * thus they should have edit access or this link should fail
     */
    public function doContentView(Request $request)
    {
        $this->wContent = ContentMap::create()->find($request->get('contentId'));
        if (!$this->wContent) {
            throw new HttpException(404, 'page not found');
        }
        $this->wPage = $this->wContent->getPage();
        if (!$this->wPage) {
            throw new HttpException(404, 'page not found');
        }
        if (!$this->wPage->canEdit($this->getFactory()->getAuthUser())) {
            $this->wPage->getPageUrl()->redirect();
        }

        if ($request->query->has('pdf')) {
            return $this->doPdf($request);
        }

        Alert::addInfo('You are viewing revision ' . $this->wContent->getContentId() . ' <a href="'.$this->wPage->getPageUrl().'">click here</a> to return to current revision');
        $this->toolbar = new ViewToolbar($this->wPage);

        return $this->getPage();
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

        $template->appendTemplate('toolbar', $this->toolbar->show());

        $template->setText('title', $this->wPage->getTitle());
        $template->setVisible('title', $this->wPage->isTitleVisible());

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

}