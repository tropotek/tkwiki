<?php
namespace App\Controller\Page;

use Bs\Db\Permissions;
use App\Table\Page;
use Bs\ControllerPublic;
use Dom\Template;
use Tk\Db;

class Manager extends ControllerPublic
{

    protected ?Page $table = null;

    public function doDefault(): void
    {
        $this->getPage()->setTitle('Page Manager');
        $this->setAccess(Permissions::PERM_SYSADMIN);
        $this->getCrumbs()->reset();

        $this->table = new \App\Table\Page();
        $this->table->setOrderBy('title');
        $this->table->setLimit(25);
        $this->table->execute();

        // Set the table rows
        $filter = $this->table->getDbFilter();
        if (!$this->getAuthUser()->isAdmin()) {
            $filter['userId'] = $this->getAuthUser()->userId;
            $filter['permission'] = \App\Db\Page::STAFF_PERMS;
        }
        $rows = \App\Db\Page::findViewable($filter);

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