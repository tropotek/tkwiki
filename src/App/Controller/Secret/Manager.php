<?php
namespace App\Controller\Secret;

use App\Db\Secret;
use App\Db\User;
use Bs\Mvc\ControllerPublic;
use Bs\Mvc\Table;
use Dom\Template;
use Tk\Alert;
use Tk\Uri;
use Tk\Db;

class Manager extends ControllerPublic
{

    protected ?Table $table = null;


    public function doDefault(): void
    {
        $this->getPage()->setTitle('Secret Manager');
        $this->getCrumbs()->reset();
        if (
            !User::getAuthUser()?->isStaff() ||
            !$this->getRegistry()->get('wiki.enable.secret.mod', false)
        ) {
            Alert::addWarning('You do not have permission to access this page');
            Uri::create('/')->redirect();
        }

        $this->table = new \App\Table\Secret();
        $this->table->setOrderBy('name');
        $this->table->setLimit(25);
        $this->table->execute();

        // Set the table rows
        $filter = $this->table->getDbFilter();
        if (!User::getAuthUser()->isAdmin()) {
            $filter['userId'] = User::getAuthUser()->userId;
            $filter['permission'] = Secret::STAFF_PERMS;
        }
        $rows = Secret::findViewable($filter);

        $this->table->setRows($rows, Db::getLastStatement()->getTotalRows());
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', $this->getPage()->getTitle());
        $template->setAttr('back', 'href', $this->getBackUrl());

        $template->setAttr('create', 'href', Uri::create('/secretEdit'));

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
      <a href="/" title="Create Secret" class="btn btn-outline-secondary" var="create"><i class="fa fa-plus"></i> Create Secret</a>
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