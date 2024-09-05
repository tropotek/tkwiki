<?php
namespace App\Controller\Page;

use App\Db\Permissions;
use App\Table\Page;
use Bs\ControllerPublic;
use Dom\Template;
use Tt\Db;
use Tt\DbFilter;

class Orphaned extends ControllerPublic
{
    protected ?Page $table = null;


    public function doDefault(): void
    {
        $this->getPage()->setTitle('Orphaned Pages');
        $this->setAccess(Permissions::PERM_EDITOR);
        $this->getCrumbs()->reset();

        $this->table = new Page();
        $this->table->execute();

        // Set the table rows
        $filter = $this->table->getDbFilter();
        $filter['orphaned'] = true;
        $rows = \App\Db\Page::findFiltered(DbFilter::create($filter, 'title'));

        $this->table->setRows($rows, Db::getLastStatement()->getTotalRows());
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());
        $template->setAttr('back', 'href', $this->getBackUrl());

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