<?php
namespace App\Controller\Page;

use App\Db\Content;
use App\Db\Page;
use App\Db\User;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Uri;

class View extends PageController
{

    protected ?Page $wPage = null;

    protected ?Content $wContent = null;


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('');

        // TODO:
        //$this->setAccess(User::PERM_SYSADMIN);
    }

    public function doDefault(Request $request, string $pageUrl)
    {
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

        $this->wContent = $this->wPage->getContent();

        // TODO: Note this should never happen (if it does then we need to look at the forign key in the DB)
//        if (!$this->wContent) {
//            // May redirect to the edit page if the user has edit privileges or send alert if not.
//            \Tk\Alert::addWarning('Page content lost, please create new content.');
//            \Tk\Uri::create('/edit')->set('pageId', $this->wPage->getId())->redirect();
//        }

        vd($request);

        if ($request->query->has('pdf')) {
            return $this->doPdf($request);
        }

        return $this->getPage();
    }

    public function doPdf(Request $request)
    {
        $rev = '';
        if ($request->query->get('contentId')) {
            $rev = '-' . $request->query->get('contentId');
        }

        $pdf = \App\Ui\Pdf::create($this->wContent->getHtml(), $this->wPage->getTitle());
        $filename = $this->wPage->getTitle().$rev.'.pdf';
        if (!$request->query->has('isHtml'))
            $pdf->output($filename);     // comment this to see html version

        return $pdf->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        //$template->appendText('title', $this->getPage()->getTitle());

        //$template->appendTemplate('content', $this->table->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
    <h1 class="" var="title"></h1>
    <div class="" var="content"></div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}