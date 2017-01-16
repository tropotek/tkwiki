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
     * @var \Tk\Db\Data
     */
    protected $data = null;

    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('WIKI Settings');
        $this->data = \Tk\Db\Data::create();
    }

    /**
     * doDefault
     *
     * @param Request $request
     * @return \App\Page\Iface
     */
    public function doDefault(Request $request)
    {
        $this->form = new Form('formEdit', $request);

        $this->form->addField(new Field\Input('site.title'))->setTabGroup('Details')->setLabel('Site Title')->setRequired(true);
        $this->form->addField(new Field\Input('site.email'))->setTabGroup('Details')->setLabel('Site Email')->setRequired(true);
        $this->form->addField(new Field\File('site.logo', $request))->setTabGroup('Details')->setLabel('Site Logo')->setAttr('accept', '.png,.jpg,.jpeg,.gif');

        $this->form->addField(new Field\Textarea('site.meta.keywords'))->setTabGroup('Details')->setLabel('META Keywords');
        $this->form->addField(new Field\Textarea('site.meta.description'))->setTabGroup('Details')->setLabel('META Description');
        
        $this->form->addField(new Field\Textarea('site.global.js'))->setTabGroup('Details')->setLabel('Global Script');
        $this->form->addField(new Field\Textarea('site.global.css'))->setTabGroup('Details')->setLabel('Global Styles');
        
        
        
        $this->form->addField(new \App\Form\ButtonInput('wiki.page.default', 'glyphicon glyphicon-folder-open'))->setTabGroup('Setup')->setLabel('Home Page')->setNotes('The default wiki home page URL');

        $this->form->addField(new Field\Checkbox('wiki.page.home.lock'))->setTabGroup('Setup')->setLabel('Lock Home Page')->setNotes('Only Allow Admin to edit the home page');
        $this->form->addField(new Field\Checkbox('site.user.registration'))->setTabGroup('Setup')->setLabel('User Registration')->setNotes('Allow users to create new accounts');
        $this->form->addField(new Field\Checkbox('site.user.activation'))->setTabGroup('Setup')->setLabel('User Activation')->setNotes('Allow users to activate their own accounts');

        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\LinkButton('cancel', \Tk\Uri::create('/')));

        $this->form->load($this->data->all());
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

        /** @var \Tk\Form\Field\File $logo */
        $logo = $form->getField('site.logo');
        
        if (!$this->form->getFieldValue('site.title')) {
            $form->addFieldError('site.title', 'Please enter your name');
        }
        if ($this->form->getFieldValue('site.email') && !filter_var($this->form->getFieldValue('site.email'), \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('site.email', 'Please enter a valid email address');
        }

        $logo->isValid();
        
        if ($this->form->hasErrors()) {
            return;
        }

        if ($logo->hasFile()) {
            $rel = '/site/logo.' . \Tk\File::getExtension($logo->getUploadedFile()->getFilename());
            $fullPath = $this->getConfig()->getDataPath() . $rel;
            $logo->moveTo($fullPath);
            $this->data->set('site.logo', $rel);

            $rel1 = '/site/favicon.' . \Tk\File::getExtension($logo->getUploadedFile()->getFilename());
            $fullPath1 = $this->getConfig()->getDataPath() . $rel1;
            $this->data->set('site.favicon', $rel1);

            \Tk\Image::create($fullPath)->squareCrop(16)->save($fullPath1);
        }
        $this->data->save();


        \Ts\Alert::addSuccess('Site settings saved.');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            \Tk\Uri::create('/')->redirect();
        }
        \Tk\Uri::create()->redirect();
    }

    /**
     * show()
     *
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();
        
        // Render the form
        $fren = new \Tk\Form\Renderer\Dom($this->form);
        $template->insertTemplate($this->form->getId(), $fren->show()->getTemplate());
        
        // Render select page dialog
        $pageSelect = new \App\Helper\PageSelect('#fid_btn_wiki\\\\.page\\\\.default', '#fid_wiki\\\\.page\\\\.default');
        $pageSelect->show();
        $template->appendTemplate('content', $pageSelect->getTemplate());

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