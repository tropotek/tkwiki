<?php
namespace App\Controller\Page;

use App\Db\Page;
use App\Table\Content;
use Bs\ControllerPublic;
use Dom\Template;
use Tk\Alert;
use Tk\Db;

class History extends ControllerPublic
{

    protected Content $table;
    protected ?Page $page = null;


    public function doDefault(): void
    {
        $this->getPage()->setTitle('Page History');

        $pageId = intval($_GET['pageId'] ?? 0);
        $this->page = Page::find($pageId);

        if (!$this->page->canEdit($this->getAuthUser())) {
            Alert::addError("You do not have permission to access this page");
            $this->getBackUrl()->redirect();
        }

        $this->getPage()->setTitle('History for `' . $this->page->title . '`');

        // Get the form template
        $this->table = new Content();
        $this->table->setOrderBy('-created');
        $this->table->setWikiPage($this->page);
        $this->table->execute();

        // Set the table rows
        $filter = $this->table->getDbFilter();
        $filter['pageId'] = $this->page->pageId;
        $rows = \App\Db\Content::findFiltered($filter);
        $this->table->setRows($rows, Db::getLastStatement()->getTotalRows());
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());
        $template->setAttr('back', 'href', $this->page->getUrl());

        $template->appendTemplate('content', $this->table->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <div class="page-actions card mb-3">
    <div class="card-header"><i class="fa fa-cogs"></i> Actions</div>
    <div class="card-body" var="actions">
      <a href="/" title="Back" class="btn btn-outline-secondary" var="back"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-users"></i> </div>
    <div class="card-body" var="content"></div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}