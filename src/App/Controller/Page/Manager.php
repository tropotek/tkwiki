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
     *
     */
    public function __construct()
    {
        parent::__construct('Page Manager');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        $this->table = new \Tk\Table('pageTable');

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('title'))->addCellCss('key')->setUrl(\Tk\Uri::create('/edit.html'));
        $this->table->addCell(new \Tk\Table\Cell\Text('userId'))->setOrderProperty('user_id');
        $this->table->addCell(new \Tk\Table\Cell\Text('type'));
        $this->table->addCell(new \Tk\Table\Cell\Text('url'));
        $this->table->addCell(new \Tk\Table\Cell\Text('permission'));
        $this->table->addCell(new \Tk\Table\Cell\Text('views'));
        $this->table->addCell(new \Tk\Table\Cell\Date('modified'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');
        
        // Actions
        $this->table->addAction(\Tk\Table\Action\Button::getInstance('New Page', 'glyphicon glyphicon-plus', \Tk\Uri::create('/edit.html')));
        $this->table->addAction(new \Tk\Table\Action\Delete());
        $this->table->addAction(new \Tk\Table\Action\Csv($this->getConfig()->getDb()));
        
        $filter = $this->table->getFilterValues();
        $users = \App\Db\PageMap::create()->findFiltered($filter, $this->table->makeDbTool('a.title'));
        $this->table->setList($users);

        return $this->show();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();
        $ren =  \Tk\Table\Renderer\Dom\Table::create($this->table);
        $ren->show();
        $template->replaceTemplate('table', $ren->getTemplate());
        return $this->getPage()->setPageContent($template);
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
      <div class="panel-heading">
        <i class="glyphicon glyphicon-th-list"></i> Pages
      </div>
      <div class="panel-body ">

        <div var="table"></div>

      </div>
    </div>
  </div>

</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }


}