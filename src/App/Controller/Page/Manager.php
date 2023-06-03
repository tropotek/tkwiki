<?php
namespace App\Controller\Page;

use App\Db\User;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Traits\SystemTrait;
use Tk\Ui\Link;
use Tk\Uri;
use Tk\Form;
use Tk\Form\Field;
use Tk\FormRenderer;
use Tk\Table;
use Tk\Table\Cell;
use Tk\Table\Action;
use Tk\TableRenderer;

class Manager extends PageController
{

    protected Table $table;

    protected ?Form $filter = null;


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Page Manager');
        $this->setAccess(User::PERM_SYSADMIN);

        $this->table = new Table('users');
        $this->filter = new Form($this->table->getId() . '-filters');
    }

    public function doDefault(Request $request)
    {

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());

        $renderer = new TableRenderer($this->getTable());
        //$renderer->setFooterEnabled(false);
        $this->getTable()->getRow()->addCss('text-nowrap');
        $this->getTable()->addCss('table-hover');

        if ($this->getFilter()) {
            $this->getFilter()->addCss('row gy-2 gx-3 align-items-center');
            $filterRenderer = FormRenderer::createInlineRenderer($this->getFilter());
            $renderer->getTemplate()->appendTemplate('filters', $filterRenderer->show());
            $renderer->getTemplate()->setVisible('filters');
        }

        $template->appendTemplate('content', $renderer->show());

        return $template;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getFilter(): ?Form
    {
        return $this->filter;
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