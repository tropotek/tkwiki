<?php
namespace App\Table;

use Bs\Table;
use Tk\Form\Field\Input;
use Tk\Traits\SystemTrait;
use Tt\Table\Cell;

class SecretSelect extends Table
{
    use SystemTrait;

    public function init(): static
    {
//        $this->table = new Table('secret-min');
//        $this->filter = new Form($this->table->getId() . '-filters');

        $this->appendCell('name')
            ->addHeaderCss('max-width')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                return sprintf('<a href="javascript:;" class="wiki-insert"
                    data-secret-id="%s" data-secret-name="%s" data-secret-url="%s" title="Insert secret link">%s</a>',
                    $obj->secretId, $obj->name, $obj->url, $obj->name);
            });

        $this->appendCell('userId')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                return $obj->getUser()?->getName() ?? '';
            });

        $this->appendCell('permission')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                return \App\Db\Secret::PERM_LIST[$obj->permission] ?? '';
            });

        // Add Filter Fields
        $this->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search: id, name');

        // init filter fields for actions to access to the filter values
        $this->initForm();

        return $this;


        //$this->getTable()->appendCell(new Cell\Checkbox('id'));
//        $this->getTable()->appendCell(new Cell\Text('name'))->setOrderByName('')->addCss('key')
//            ->addOnValue(function (Cell\Text $cell) {
//                /** @var \App\Db\Secret $obj */
//                $obj = $cell->getRow()->getData();
//                $cell->setUrlProperty('');
//                $cell->setUrl(Uri::create('javascript:;'));
//                $cell->getLink()->addCss('wiki-insert');
//                $cell->getLink()->setAttr('data-secret-id', $obj->secretId);
//                $cell->getLink()->setAttr('data-secret-name', $obj->name);
//                $cell->getLink()->setAttr('data-secret-url', $obj->url);
//            });
//        $this->getTable()->appendCell(new Cell\Text('userId'))->setOrderByName('')
//            ->addOnValue(function (Cell\Text $cell) {
//                /** @var \App\Db\Secret $obj */
//                $obj = $cell->getRow()->getData();
//                $cell->setValue($obj->getUser()->getName());
//            });
//        $this->getTable()->appendCell(new Cell\Text('permission'))
//            ->addOnValue(function (Cell\Text $cell, mixed $value) {
//                return \App\Db\Secret::PERM_LIST[$value] ?? '';
//            });

//        // Table filters
//        $this->getFilter()->appendField(new Field\Input('search'))->setAttr('placeholder', 'Search');
//
//        // Load filter values
//        $this->getFilter()->setFieldValues($this->getTable()->getTableSession()->get($this->getFilter()->getId(), []));
//
//        $this->getFilter()->appendField(new Form\Action\Submit('Search', function (Form $form, Form\Action\ActionInterface $action) {
//            $this->getTable()->getTableSession()->set($this->getFilter()->getId(), $form->getFieldValues());
//            Uri::create()->redirect();
//        }))->setGroup('');
//        $this->getFilter()->appendField(new Form\Action\Submit('Clear', function (Form $form, Form\Action\ActionInterface $action) {
//            $this->getTable()->getTableSession()->set($this->getFilter()->getId(), []);
//            Uri::create()->redirect();
//        }))->setGroup('')->addCss('btn-outline-secondary');
//
//        $this->getFilter()->execute($request->request->all());

    }

//    public function show(): ?Template
//    {
//        //$renderer = new TableRenderer($this->getTable());
//        //$this->getTable()->getRow()->addCss('text-nowrap');
//        //$renderer->getFooterList()->remove('limit');
//        //$renderer->getTemplate()->addCss('tk-table', 'secret-table');
//
//
//        $this->getForm()->addCss('row gy-2 gx-3 align-items-center');
//        //$filterRenderer = Form\Renderer\Dom\Renderer::createInlineRenderer($this->getFilter());
//        //$renderer->getTemplate()->appendTemplate('filters', $filterRenderer->show());
//        //$renderer->getTemplate()->setVisible('filters');
//
//        return parent::show();
//    }

}