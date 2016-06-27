<?php
namespace App\Controller\Admin;

use Tk\Request;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;
use App\Controller\Iface;

/**
 * Class Contact
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Settings extends Iface
{

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \App\Db\Data|null
     */
    protected $data = null;

    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('WIKI Settings', \App\Auth\Access::ROLE_ADMIN);
        $this->data = \App\Db\Data::create();
    }

    /**
     * doDefault
     *
     * @param Request $request
     * @return \App\Page\PublicPage
     */
    public function doDefault(Request $request)
    {
        $this->form = new Form('formEdit', $request);

        $this->form->addField(new Field\Input('site.title'))->setLabel('Site Title')->setRequired(true);
        $this->form->addField(new Field\Input('site.email'))->setLabel('Site Email')->setRequired(true);
        // TODO: Add a look up dialog 
        $this->form->addField(new \App\Form\ButtonInput('wiki.page.default', 'glyphicon glyphicon-folder-open'))->setLabel('Home Page')->setNotes('The default wiki home page URL');
        $this->form->addField(new Field\Checkbox('site.user.registration'))->setLabel('User Registration')->setNotes('Allow users to create new accounts');
        $this->form->addField(new Field\Checkbox('site.user.activation'))->setLabel('User Activation')->setNotes('Allow users to activate their own accounts');
        
        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Link('cancel', \Tk\Uri::create('/')));

        $this->form->load($this->data);
        $this->form->execute();

        return $this->show();
    }

    /**
     * doSubmit()
     *
     * @param Form $form
     */
    public function doSubmit($form)
    {
        $values = $form->getValues();
        $this->data->replace($values);
        
        if (empty($values['site.title']) || strlen($values['site.title']) < 3) {
            $form->addFieldError('site.title', 'Please enter your name');
        }
        if (empty($values['site.email']) || !filter_var($values['site.email'], \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('site.email', 'Please enter a valid email address');
        }
        
        if ($this->form->hasErrors()) {
            return;
        }
        
        $this->data->save();
        
        \App\Alert::addSuccess('Site settings saved.');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            \Tk\Uri::create('/')->redirect();
        }
        \Tk\Uri::create()->redirect();
    }

    /**
     * show()
     *
     * @return \App\Page\PublicPage
     */
    public function show()
    {
        $template = $this->getTemplate();
        
        // Render the form
        $fren = new \Tk\Form\Renderer\Dom($this->form);
        $template->insertTemplate($this->form->getId(), $fren->show()->getTemplate());



        $listUrl = \Tk\Uri::create('/ajax/getPageList')->toString();
        $js = <<<JS
jQuery(function($) {
  
  $(document.getElementById('fid_btn_wiki.page.default')).on('click', function(e) {
    $('#pageSelectModal').modal('show');
  });
  
});
JS;
        $template->appendJs($js);
        $pageSelect = new \App\Helper\PageSelect();
        $pageSelect->show();
        $template->appendTemplate('content', $pageSelect->getTemplate());
        
        
        $listUrl = \Tk\Uri::create('/ajax/getPageList');
        $js = <<<JS
jQuery(function($) {
  
  $('.pageList').pageList({
    ajaxUrl : '$listUrl',
    onPageSelect : function (page) {
      $(document.getElementById('fid_wiki.page.default')).val(page.url);
      $('#pageSelectModal').modal('hide');
      console.log('Setting input to - ' + page.url);
    }
  })
  
});
JS;
        $template->appendJs($js);
        
        return $this->getPage()->setPageContent($template);
    }



    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="row" var="content">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="glyphicon glyphicon-cog"></i>
        Site Settings
      </div>
      <!-- /.panel-heading -->
      <div class="panel-body">
        <div class="row">
          <div class="col-lg-12">

            <div var="formEdit"></div>

          </div>
        </div>
      </div>
      <!-- /.panel-body -->
    </div>
    <!-- /.panel -->
  </div>
</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }
}