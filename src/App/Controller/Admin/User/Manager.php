<?php
namespace App\Controller\Admin\User;

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
        parent::__construct('User Manager', \App\Auth\Access::ROLE_ADMIN);
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        $this->table = new \Tk\Table('userTable');

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCellCss('key')->setUrl(\Tk\Uri::create('userEdit.html'));
        $this->table->addCell(new \Tk\Table\Cell\Text('username'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        //$this->table->addCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        //$this->table->addCell(new \Tk\Table\Cell\Date('created'))->setFormat(\Tk\Table\Cell\Date::FORMAT_RELATIVE);
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');
        

        // Actions
        $this->table->addAction(\Tk\Table\Action\Button::getInstance('New User', 'glyphicon glyphicon-plus', \Tk\Uri::create('userEdit.html')));
        $this->table->addAction(new \Tk\Table\Action\Delete());
        $this->table->addAction(new \Tk\Table\Action\Csv($this->getConfig()->getDb()));
        
        $filter = $this->table->getFilterValues();
        $users = \App\Db\UserMap::create()->findFiltered($filter, $this->table->makeDbTool('a.name'));
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
        <i class="fa fa-users fa-fw"></i> Users
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