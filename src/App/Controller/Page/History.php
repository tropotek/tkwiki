<?php
namespace App\Controller\Page;

use App\Db\ContentMap;
use App\Db\Page;
use App\Db\PageMap;
use App\Db\User;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;

class History extends PageController
{

    protected \App\Table\Content $table;

    protected ?Page $wPage = null;


    public function __construct()
    {
        parent::__construct();
        $this->getPage()->setTitle('Page History');
        $this->setAccess(User::PERM_EDITOR);
    }

    public function doDefault(Request $request): \App\Page|\Dom\Mvc\Page
    {
        $this->wPage = PageMap::create()->find($request->query->getInt('pageId'));
        $this->getPage()->setTitle('History for `' . $this->wPage->getTitle() . '`');

        // Get the form template
        $this->table = new \App\Table\Content();
        $this->table->doDefault($request, $this->wPage->getPageId());

        $tool = $this->table->getTable()->getTool('created DESC');
        $filter = $this->table->getFilter()->getFieldValues();
        $filter['pageId'] = $this->wPage->getPageId();
        $list = ContentMap::create()->findFiltered($filter, $tool);
        $this->table->execute($request, $list);

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());
        $template->setAttr('back', 'href', $this->wPage->getPageUrl());

        $template->appendTemplate('content', $this->table->show());

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