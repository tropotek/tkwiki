<?php
namespace App\Controller\Page;

use App\Db\Content;
use App\Db\Page;
use App\Helper\ViewToolbar;
use App\Util\Pdf;
use Bs\ControllerPublic;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tk\Alert;
use Tk\Uri;

class View extends ControllerPublic
{

    protected ?Page        $wPage    = null;
    protected ?Content     $wContent = null;
    protected ?ViewToolbar $toolbar  = null;


    public function doDefault(Request $request, string $pageUrl)
    {
        if ($pageUrl == Page::DEFAULT_TAG) {
            $pageUrl = Page::getHomePage()->url;
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
                Alert::addWarning('You do not have permission to view the page: `' . $this->wPage->title . '`');
                Page::getHomePage()->getUrl()->redirect();
            }
        }

        $this->wPage->views++;
        $this->wPage->save();

        $this->getPage()->setTitle($this->wPage->title);
        $this->wContent = $this->wPage->getContent();
        $this->toolbar = new ViewToolbar($this->wPage);

        if (isset($_GET['pdf'])) {
            return $this->doPdf();
        }

    }

    /**
     * This method is used for system users viewing wiki pages
     * thus they should have edit access or this link should fail
     */
    public function doContentView()
    {
        $this->wContent = Content::find($_GET['contentId'] ?? 0);
        if (!$this->wContent) {
            throw new HttpException(404, 'page not found');
        }
        $this->wPage = $this->wContent->getPage();
        if (!$this->wPage) {
            throw new HttpException(404, 'page not found');
        }
        if (!$this->wPage->canEdit($this->getFactory()->getAuthUser())) {
            $this->wPage->getUrl()->redirect();
        }

        if (isset($_GET['pdf'])) {
            return $this->doPdf();
        }

        Alert::addInfo('You are viewing revision ' . $this->wContent->contentId .
            ' <a href="'.$this->wPage->getUrl().'">click here</a> to return to current revision');
        $this->toolbar = new ViewToolbar($this->wPage);

    }

    public function doPdf()
    {
        $rev = '';
        if (isset($_GET['contentId'])) {
            $rev = '-' . $_GET['contentId'];
        }

        $pdf = Pdf::create($this->wContent->html, $this->wPage->title);
        $filename = $this->wPage->title.$rev.'.pdf';

        if (!isset($_GET['isHtml'])) {
            $pdf->output($filename);     // comment this to see html version
        }

        return $pdf->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $template->appendTemplate('toolbar', $this->toolbar->show());

        $template->setText('title', $this->wPage->title);
        $template->setVisible('title', $this->wPage->titleVisible);

        $template->setHtml('content', $this->wContent->html);

        if ($this->wContent->css) {
            $template->appendCss($this->wContent->css);
        }
        if ($this->wContent->js) {
            $template->appendJs($this->wContent->js);
        }

        if ($this->wContent->keywords) {
            $this->getPage()->getTemplate()->appendMetaTag('keywords', $this->wContent->keywords, $this->getPage()->getTemplate()->getTitleElement());
        }

        if ($this->wContent->description) {
            $this->getPage()->getTemplate()->appendMetaTag('description', $this->wContent->description, $this->getPage()->getTemplate()->getTitleElement());
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