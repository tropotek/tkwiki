<?php
namespace App\Controller\Secret;

use App\Db\Secret;
use Bs\ControllerPublic;
use Bs\Table;
use Dom\Template;
use Tk\Alert;
use Tk\Uri;
use Tt\Db;
use Tt\DbFilter;

/**
 *
 */
class Manager extends ControllerPublic
{

    protected ?Table $table = null;


    public function doDefault(): void
    {
        $this->getPage()->setTitle('Secret Manager');
        $this->getCrumbs()->reset();
        if (
            !$this->getAuthUser()?->isStaff() ||
            !$this->getRegistry()->get('wiki.enable.secret.mod', false)
        ) {
            Alert::addWarning('You do not have permission to access this page');
            Uri::create('/')->redirect();
        }

        $this->table = new \App\Table\Secret();
        $this->table->execute();

        // Set the table rows
        $filter = $this->table->getDbFilter();
        $filter['userId'] = $this->getAuthUser()->userId;
        $rows = Secret::findFiltered(DbFilter::create($filter, 'name'));

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