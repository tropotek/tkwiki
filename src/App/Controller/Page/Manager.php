<?php
namespace App\Controller\Page;

use App\Db\Page;
use App\Db\PageMap;
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
        $this->getTable()->appendCell(new Cell\Checkbox('id'));
        $this->getTable()->appendCell(new Cell\Text('title'))->addCss('key');
        $this->getTable()->appendCell(new Cell\Text('userId'))
            ->addOnValue(function (Cell\Text $cell) {
                /** @var Page $page */
                $page = $cell->getRow()->getData();
                $cell->setValue($page->getUser()->getName());
                vd($cell->getValue());
                // TODO: add the edit URL

            });
        $this->getTable()->appendCell(new Cell\Text('type'));
        $this->getTable()->appendCell(new Cell\Text('url'));
        $this->getTable()->appendCell(new Cell\Boolean('published'));
        $this->getTable()->appendCell(new Cell\Text('permission'))
            ->addOnValue(function (Cell\Text $cell) {
                /** @var Page $page */
                $page = $cell->getRow()->getData();
                $cell->setValue($page->getPermissionLabel());
            });;
        $this->getTable()->appendCell(new Cell\Text('modified'));
        $this->getTable()->appendCell(new Cell\Text('created'));


        // Table filters
        $this->getFilter()->appendField(new Field\Input('search'))->setAttr('placeholder', 'Search');
        $list = [
            '-- Type --' => '',
            Page::TYPE_PAGE => Page::TYPE_PAGE,
            Page::TYPE_NAV => Page::TYPE_NAV,
        ];
        $this->getFilter()->appendField(new Field\Select('type', $list));

        // Load filter values
        $this->getFilter()->setFieldValues($this->getTable()->getTableSession()->get($this->getFilter()->getId(), []));

        $this->getFilter()->appendField(new Form\Action\Submit('Search', function (Form $form, Form\Action\ActionInterface $action) {
            $this->getTable()->getTableSession()->set($this->getFilter()->getId(), $form->getFieldValues());
            Uri::create()->redirect();
        }))->setGroup('');
        $this->getFilter()->appendField(new Form\Action\Submit('Clear', function (Form $form, Form\Action\ActionInterface $action) {
            $this->getTable()->getTableSession()->set($this->getFilter()->getId(), []);
            Uri::create()->redirect();
        }))->setGroup('')->addCss('btn-outline-secondary');

        $this->getFilter()->execute($request->request->all());


        // Table Actions
        if ($this->getConfig()->isDebug()) {
            $this->getTable()->appendAction(new Action\Link('reset', Uri::create()->set(Table::RESET_TABLE, $this->getTable()->getId()), 'fa fa-retweet'))
                ->setLabel('')
                ->setAttr('data-confirm', 'Are you sure you want to reset the Table`s session?')
                ->setAttr('title', 'Reset table filters and order to default.');
        }
        $this->getTable()->appendAction(new Action\Button('Create'))->setUrl(Uri::create('/pageEdit'));
        $this->getTable()->appendAction(new Action\Delete());
        $this->getTable()->appendAction(new Action\Csv())->addExcluded('actions');


        // Query
        $tool = $this->getTable()->getTool();
        $filter = $this->getFilter()->getFieldValues();
        $list = PageMap::create()->findFiltered($filter, $tool);
        $this->getTable()->setList($list, $tool->getFoundRows());

        $this->getTable()->execute($request);


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