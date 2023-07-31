<?php
namespace App\Controller\Page;

use App\Db\User;
use Bs\PageController;
use Bs\Table\ManagerTrait;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;

class Manager extends PageController
{
    use ManagerTrait;


    public function __construct()
    {
        parent::__construct();
        $this->getPage()->setTitle('Page Manager');
        $this->setAccess(User::PERM_EDITOR);
        $this->getCrumbs()->reset();
    }

    public function doDefault(Request $request): \App\Page|\Dom\Mvc\Page
    {
        $this->setTable(new \App\Table\Page());
        $this->getTable()->init();
        $this->getTable()->findList([], $this->getTable()->getTool('title'));
        $this->getTable()->execute($request);

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());

        $template->appendTemplate('content', $this->getTable()->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <div class="card mb-3">
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