<?php
namespace App\Table;

use App\Db\SecretMap;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Db\Mapper\Result;
use Tk\Traits\SystemTrait;
use Tk\Uri;
use Tk\Form;
use Tk\Form\Field;
use Tk\Table;
use Tk\Table\Cell;
use Tk\TableRenderer;

class SecretSelect
{
    use SystemTrait;

    protected Table $table;

    protected ?Form $filter = null;


    public function __construct()
    {
        $this->table = new Table('secret-min');
        $this->filter = new Form($this->table->getId() . '-filters');
    }

    public function doDefault(Request $request): void
    {
        //$this->getTable()->appendCell(new Cell\Checkbox('id'));
        $this->getTable()->appendCell(new Cell\Text('name'))->setOrderByName('')->addCss('key')
            ->addOnValue(function (Cell\Text $cell) {
                /** @var \App\Db\Secret $obj */
                $obj = $cell->getRow()->getData();
                $cell->setUrlProperty('');
                $cell->setUrl(Uri::create('javascript:;'));
                $cell->getLink()->addCss('wiki-insert');
                $cell->getLink()->setAttr('data-secret-id', $obj->getSecretId());
                $cell->getLink()->setAttr('data-secret-name', $obj->getName());
                $cell->getLink()->setAttr('data-secret-url', $obj->getUrl());
            });
        $this->getTable()->appendCell(new Cell\Text('userId'))->setOrderByName('')
            ->addOnValue(function (Cell\Text $cell) {
                /** @var \App\Db\Secret $obj */
                $obj = $cell->getRow()->getData();
                $cell->setValue($obj->getUser()->getName());
            });
        $this->getTable()->appendCell(new Cell\Text('permission'))
            ->addOnValue(function (Cell\Text $cell, mixed $value) {
                return \App\Db\Secret::PERM_LIST[$value] ?? '';
            });

        // Table filters
        $this->getFilter()->appendField(new Field\Input('search'))->setAttr('placeholder', 'Search');

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

    }

    public function execute(Request $request, ?Result $list = null): void
    {
        // Query
        if (!$list) {
            $tool = $this->getTable()->getTool();
            $filter = $this->getFilter()->getFieldValues();
            $list = SecretMap::create()->findFiltered($filter, $tool);
        }
        $this->getTable()->setList($list);

        $this->getTable()->execute($request);
    }

    public function show(): ?Template
    {
        $renderer = new TableRenderer($this->getTable());
        $this->getTable()->getRow()->addCss('text-nowrap');
        $renderer->getFooterList()->remove('limit');
        $renderer->getTemplate()->addCss('tk-table', 'secret-table');

        if ($this->getFilter()) {
            $this->getFilter()->addCss('row gy-2 gx-3 align-items-center');
            $filterRenderer = Form\Renderer\Dom\Renderer::createInlineRenderer($this->getFilter());
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