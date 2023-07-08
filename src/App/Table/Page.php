<?php
namespace App\Table;

use App\Db\PageMap;
use App\Db\UserMap;
use App\Util\Masquerade;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Db\Mapper\Result;
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

class Page
{
    use SystemTrait;

    protected Table $table;

    protected ?Form $filter = null;


    public function __construct()
    {
        $this->table = new Table('pages');
        $this->filter = new Form($this->table->getId() . '-filters');
    }

    public function doDefault(Request $request)
    {
        $this->getTable()->appendCell(new Cell\Checkbox('pageId'));
        $this->getTable()->appendCell(new Cell\Text('actions'))
            ->addOnShow(function (Cell\Text $cell, string $html) {
                $cell->addCss('text-nowrap text-center');
                $obj = $cell->getRow()->getData();

                $template = $cell->getTemplate();
                $btn = new Link('WikiLink');
                $btn->setText('');
                $btn->setIcon('fa fa-fw fa-code');
                $btn->addCss('btn btn-outline-secondary btn-copy-code');
                $btn->setAttr('title', 'Click to copy wiki link');
                $btn->setAttr('data-page-id', $obj->getPageId());
                $template->appendTemplate('td', $btn->show());
                //$template->appendHtml('td', '&nbsp;');
                $js = <<<JS
jQuery(function($) {

    $('.btn-copy-code').on('click', function () {
        let tr = $(this).closest('tr');
        let url = $('.mUrl', tr).text();
        let title = $('.mTitle a', tr).text();
        let code = `<p>&lt;a href="page://\${url}" title="\${title}"&gt;\${title}&lt;/a&gt;</p>`;
        copyToClipboard($(code).text());
    });

});
JS;
                $template->appendJs($js);

                return '';
            });
        $this->getTable()->appendCell(new Cell\Text('title'))
            ->addCss('key')
            ->setUrlProperty('pageId')
            ->setUrl(Uri::create('/edit'));
        $this->getTable()->appendCell(new Cell\Text('category'));
        $this->getTable()->appendCell(new Cell\Text('url'));
        $this->getTable()->appendCell(new Cell\Boolean('published'));
        $this->getTable()->appendCell(new Cell\Text('permission'))
            ->addOnValue(function (Cell\Text $cell) {
                /** @var \App\Db\Page $page */
                $page = $cell->getRow()->getData();
                $cell->setValue($page->getPermissionLabel());
            });
        $this->getTable()->appendCell(new Cell\Text('userId'))
            ->addOnValue(function (Cell\Text $cell) {
                /** @var \App\Db\Page $page */
                $page = $cell->getRow()->getData();
                $cell->setValue($page->getUser()->getName());
            });
        $this->getTable()->appendCell(new Cell\Text('modified'));
        $this->getTable()->appendCell(new Cell\Text('created'));


        // Table filters
        $this->getFilter()->appendField(new Field\Input('search'))->setAttr('placeholder', 'Search');

        $list = PageMap::create()->getCategoryList();
        $this->getFilter()->appendField(new Field\Select('category', $list))->prependOption('-- Category -- ', '');


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
        if ($this->getFactory()->getAuthUser()->hasPermission(\App\Db\User::PERM_SYSADMIN)) {
            $this->getTable()->appendAction(new Action\Delete('delete', 'pageId'))
                ->addOnDelete(function (Action\Delete $action, \App\Db\Page $obj) {
                    if (!$obj->canDelete($this->getFactory()->getAuthUser())) {
                        Alert::addWarning('You do not have permission to delete this page.');
                        return false;
                    }
                });
        }
        $this->getTable()->appendAction(new Action\Csv('csv', 'pageId'))->addExcluded('actions');

    }

    public function execute(Request $request, ?Result $list = null): void
    {
        // Query
        if (!$list) {
            $tool = $this->getTable()->getTool();
            $filter = $this->getFilter()->getFieldValues();
            $list = PageMap::create()->findFiltered($filter, $tool);
        }
        $this->getTable()->setList($list);

        $this->getTable()->execute($request);
    }

    public function show(): ?Template
    {
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

        return $renderer->show();
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getFilter(): ?Form
    {
        return $this->filter;
    }
}