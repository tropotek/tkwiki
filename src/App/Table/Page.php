<?php
namespace App\Table;

use App\Db\PageMap;
use Bs\Table\ManagerInterface;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Db\Mapper\Result;
use Tk\Db\Tool;
use Tk\Ui\Link;
use Tk\Uri;
use Tk\Form\Field;
use Tk\Table\Cell;
use Tk\Table\Action;

class Page extends ManagerInterface
{

    public function initCells(): void
    {
        //$this->resetTableSession();
        $this->appendCell(new Cell\RowSelect('pageId'));
        $this->appendCell(new Cell\Text('actions'))
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
        $this->appendCell(new Cell\Text('title'))
            ->addCss('key')
            ->setUrlProperty('pageId')
            ->setUrl(Uri::create('/edit'));
        $this->appendCell(new Cell\Text('category'));
        $this->appendCell(new Cell\Text('url'));
        $this->appendCell(new Cell\Boolean('published'));
        $this->appendCell(new Cell\Text('permission'))
            ->addOnValue(function (Cell\Text $cell) {
                /** @var \App\Db\Page $page */
                $page = $cell->getRow()->getData();
                $cell->setValue($page->getPermissionLabel());
            });
        $this->appendCell(new Cell\Text('userId'))
            ->addOnValue(function (Cell\Text $cell) {
                /** @var \App\Db\Page $page */
                $page = $cell->getRow()->getData();
                $cell->setValue($page->getUser()->getName());
            });
        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));


        // Table filters
        $this->getFilterForm()->appendField(new Field\Input('search'))->setAttr('placeholder', 'Search');

        $list = \App\Db\Page::getCategoryList();
        $this->getFilterForm()->appendField(new Field\Select('category', $list))->prependOption('-- Category -- ', '');

        // Table Actions
        if ($this->getFactory()->getAuthUser()->hasPermission(\App\Db\User::PERM_SYSADMIN)) {
            $this->appendAction(new Action\Delete('delete', 'pageId'))
                ->addOnDelete(function (Action\Delete $action, \App\Db\Page $obj) {
                    if (!$obj->canDelete($this->getFactory()->getAuthUser())) {
                        Alert::addWarning('You do not have permission to delete this page.');
                        return false;
                    }
                });
        }
        $this->appendAction(new Action\Csv('csv', 'pageId'))->addExcluded('actions');

    }

    public function execute(Request $request): static
    {
        return parent::execute($request);
    }

    public function findList(array $filter = [], ?Tool $tool = null): null|array|Result
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterForm()->getFieldValues(), $filter);

        $list = \App\Db\Page::findFiltered($filter);
        //$list = PageMap::create()->findFiltered($filter, $tool);
        $this->setList($list);
        return $list;
    }

    public function show(): ?Template
    {
        $renderer = $this->getTableRenderer();
        $this->getRow()->addCss('text-nowrap');
        $this->showFilterForm();
        return $renderer->show();
    }

}