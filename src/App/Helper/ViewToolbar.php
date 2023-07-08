<?php
namespace App\Helper;

use App\Db\Content;
use App\Db\Page;
use App\Db\User;
use Bs\Ui\Dialog;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Date;
use Tk\Traits\SystemTrait;
use Tk\Uri;

/**
 * The view page toolbar button group and its actions
 */
class ViewToolbar extends Renderer implements DisplayInterface
{
    use SystemTrait;

    protected Page $page;

    protected Content $content;

    protected ?User $user = null;


    public function __construct(Page $page)
    {
        $this->page = $page;
        $this->content = $page->getContent();
        $this->user = $this->getFactory()->getAuthUser();
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        if ($this->getPage()->canEdit($this->getUser())) {
            $template->setAttr('edit-url', 'href', Uri::create('/edit')->set('pageId', $this->getPage()->getPageId()));
            $template->setAttr('history', 'href', Uri::create('/historyManager')->set('pageId', $this->getPage()->getPageId()));
            $template->setVisible('can-edit');
        }
        if ($this->getFactory()->getAuthUser()?->isStaff()) {
            $template->setVisible('info-url');
            $dialog = $this->showInfoDialog();
            $template->appendBodyTemplate($dialog->show());
            $template->setAttr('info-url', 'data-bs-toggle', 'modal');
            $template->setAttr('info-url', 'data-bs-target', '#'.$dialog->getId());
        }
        $template->setAttr('pdf-url', 'href', Uri::create()->set('pdf'));

        if ($this->getRequest()->get('contentId')) {
            $template->addCss('group', 'revision');
        }

        return $template;
    }

    protected function showInfoDialog(): Dialog
    {
        $dialog = new Dialog('Page Information', 'page-info-dialog');
        $html = <<<HTML
<ul class="list-unstyled">
  <li>Author: <span var="author"></span></li>
  <li>Title: <span var="title"></span></li>
  <li>Category: <span var="category"></span></li>
  <li>Permission: <span var="permission"></span></li>
  <li>Current Revision: <span var="revision"></span></li>
  <li>Modified: <span var="modified"></span></li>
  <li>Created: <span var="created"></span></li>
</ul>
HTML;
        $t = $this->loadTemplate($html);

        $t->setText('author', $this->page->getUser()->getName());
        $t->setText('title', $this->page->getTitle());
        $t->setText('category', $this->page->getCategory());
        $t->setText('permission', $this->page->getPermissionLabel());
        $t->setText('revision', $this->content->getContentId());
        $t->setText('modified', $this->page->getModified(Date::FORMAT_LONG_DATETIME));
        $t->setText('created', $this->page->getCreated(Date::FORMAT_LONG_DATETIME));

        $dialog->setContent($t);
        return $dialog;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="wk-toolbar btn-group btn-group-sm float-end" role="group" aria-label="Small button group" var="group">
  <a href="/edit?pageId=1" title="Edit The Page" class="btn btn-outline-secondary" choice="can-edit" var="edit-url"><i class="fa fa-fw fa-pencil"></i></a>
  <a href="javascript:;" title="Page History" class="btn btn-outline-secondary" choice="can-edit" var="history"><i class="fa fa-fw fa-clock-rotate-left"></i></a>
  <a href="/?pdf=pdf" title="Download PDF" class="btn btn-outline-secondary" target="_blank" var="pdf-url"><i class="fa fa-fw fa-file-pdf"></i></a>
  <a href="javascript:window.print();" title="Print Document" class="btn btn-outline-secondary"><i class="fa fa-fw fa-print"></i></a>
  <a href="javascript:;" title="Page Info" class="btn btn-outline-secondary" choice="info-url"><i class="fa fa-fw fa-circle-info"></i></a>
</div>
HTML;

        return $this->loadTemplate($html);
    }

}
