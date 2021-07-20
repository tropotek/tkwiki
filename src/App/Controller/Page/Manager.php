<?php
namespace App\Controller\Page;

use Tk\Request;
use Dom\Template;
use Tk\Form\Field;
use App\Controller\Iface;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends Iface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;


    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->table = \Tk\Table::create('pageTable');

        $this->table->appendCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->appendCell(new \Tk\Table\Cell\Text('title'))->addCss('key')->setUrl(\Tk\Uri::create('/user/edit.html'));
        $this->table->appendCell(new \Tk\Table\Cell\Text('userId'))->setOrderProperty('user_id');
        $this->table->appendCell(new \Tk\Table\Cell\Text('type'));
        $this->table->appendCell(new \Tk\Table\Cell\Text('url'));
        $this->table->appendCell(new \Tk\Table\Cell\Text('permission'))->addOnPropertyValue(function($cell, $obj, $value) {
            /** @var \App\Db\Page $obj */
            if ($obj->getPermissionLabel()) return ucwords($obj->getPermissionLabel());
            return $value;

        });
        $this->table->appendCell(new \Tk\Table\Cell\Text('views'));
        $this->table->appendCell(new \Tk\Table\Cell\Date('modified'));
        $this->table->appendCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->appendFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        $this->table->appendAction(\Tk\Table\Action\Button::createButton('New Page', 'fa fa-plus', \Tk\Uri::create('/edit.html')));
        $this->table->appendAction(new \Tk\Table\Action\Delete());
        $this->table->appendAction(new \Tk\Table\Action\Csv($this->getConfig()->getDb()));

        $filter = $this->table->getFilterValues();
        $list = \App\Db\PageMap::create()->findFiltered($filter, $this->table->getTool('a.title'));
        $this->table->setList($list);

    }

    /**
     * @return Template
     */
    public function show()
    {
        $template = parent::show();

        $ren =  \Tk\Table\Renderer\Dom\Table::create($this->table);
        $template->appendTemplate('table', $ren->show());

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="row">

  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading"><i class="fa fa-th-list"></i> Pages</div>
      <div class="panel-body" var="table"></div>
    </div>
  </div>

</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }


}
