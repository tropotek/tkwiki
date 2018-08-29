<?php
namespace App\Controller\Admin;

use Tk\Request;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Settings extends \Bs\Controller\AdminIface
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
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('WIKI Settings');
        $this->data = \Tk\Db\Data::create();

        $this->form = $this->getConfig()->createForm('site-settings');
        $this->form->setRenderer($this->getConfig()->createFormRenderer($this->form));

        $tab = 'Site';
        $this->form->addField(new Field\Input('site.title'))->setTabGroup($tab)->setLabel('Site Title')->setRequired(true);
        $this->form->addField(new Field\Input('site.email'))->setTabGroup($tab)->setLabel('Site Email')->setRequired(true);
        $this->form->addField(new Field\File('site.logo', '/site'))->setTabGroup($tab)->setLabel('Site Logo')
            ->addCss('tk-imageinput')->setAttr('accept', '.png,.jpg,.jpeg,.gif');

        $tab = 'Template';
        $this->form->addField(new Field\Input('site.meta.keywords'))->setTabGroup($tab)->setLabel('META Keywords');
        $this->form->addField(new Field\Input('site.meta.description'))->setTabGroup($tab)->setLabel('META Description');

        $this->form->appendField(new Field\Textarea('site.global.css'))->setAttr('id', 'site-global-css')->setTabGroup($tab)
            ->setLabel('Custom Styles')->setNotes('You can omit the &lt;style&gt; tags here')->addCss('code')
            ->setAttr('data-mode', 'css');
        $this->form->appendField(new Field\Textarea('site.global.js'))->setAttr('id', 'site-global-js')->setTabGroup($tab)
            ->setLabel('Custom JS')->setNotes('You can omit the &lt;script&gt; tags here')->addCss('code')
            ->setAttr('data-mode', 'javascript');

//        $this->form->addField(new Field\Textarea('site.global.js'))->setTabGroup($tab)->setLabel('Global Script');
//        $this->form->addField(new Field\Textarea('site.global.css'))->setTabGroup($tab)->setLabel('Global Styles');

        $tab = 'Config';
        $this->form->addField(new \App\Form\ButtonInput('wiki.page.default', 'glyphicon glyphicon-folder-open'))->setTabGroup($tab)
            ->setLabel('Home Page')->setNotes('The default wiki home page URL');

        $this->form->addField(new Field\Checkbox('wiki.page.home.lock'))->setTabGroup($tab)->setLabel('Lock Home Page')
            ->setNotes('Only Allow Admin to edit the home page');
        $this->form->addField(new Field\Checkbox('site.user.registration'))->setTabGroup($tab)->setLabel('User Registration')
            ->setNotes('Allow users to create new accounts');
        $this->form->addField(new Field\Checkbox('site.user.activation'))->setTabGroup($tab)->setLabel('User Activation')
            ->setNotes('Allow users to activate their own accounts');
        $this->form->addField(new Field\Checkbox('site.page.header.hide'))->setTabGroup($tab)->setLabel('Hide Header Info')
            ->setNotes('Hide the page header info from public view.');
        $this->form->addField(new Field\Checkbox('site.page.header.title.hide'))->setTabGroup($tab)->setLabel('Hide Header Title')
            ->setNotes('Hide the page header title from public view.');

        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\LinkButton('cancel', \Tk\Uri::create('/')));

        $this->form->load($this->data->all());
        $this->form->execute();

    }

    /**
     * @param Form $form
     * @throws Form\Exception
     * @throws \Tk\Exception
     */
    public function doSubmit($form)
    {
        $values = $form->getValues();
        $this->data->replace($values);
        
        if (!$this->form->getFieldValue('site.title')) {
            $form->addFieldError('site.title', 'Please enter your name');
        }
        if ($this->form->getFieldValue('site.email') && !filter_var($this->form->getFieldValue('site.email'), \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('site.email', 'Please enter a valid email address');
        }


        /** @var \Tk\Form\Field\File $logo */
        $logo = $form->getField('site.logo');
        $logo->isValid();
        
        if ($this->form->hasErrors()) {
            return;
        }
        
        $logo->saveFile();
        if ($logo->hasFile()) {
            $fullPath = $this->getConfig()->getDataPath() . $logo->getValue();
            \Tk\Image::create($fullPath)->bestFit(256, 256)->save();
            $this->data->set('site.logo', $logo->getValue());
            // Create favicon
            $rel1 = '/site/favicon.' . \Tk\File::getExtension($logo->getUploadedFile()->getFilename());
            \Tk\Image::create($fullPath)->squareCrop(16)->save($this->getConfig()->getDataPath() . $rel1);
            $this->data->set('site.favicon', $rel1);
        }
        $this->data->save();


        \Tk\Alert::addSuccess('Site settings saved.');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            \Tk\Uri::create('/')->redirect();
        }
        \Tk\Uri::create()->redirect();
    }

    /**
     * @return \Dom\Template
     * @throws \Dom\Exception
     */
    public function show()
    {
        $template = parent::show();
        
        // Render the form
        $template->insertTemplate('form', $this->form->getRenderer()->show());
        
        // Render select page dialog
        $pageSelect = new \App\Helper\PageSelect('#fid_btn_wiki\\\\.page\\\\.default', '#fid_wiki\\\\.page\\\\.default');
        $pageSelect->show();
        $template->appendTemplate('content', $pageSelect->getTemplate());

        return $template;
    }



    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="" var="content">
  
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="glyphicon glyphicon-cog"></i> Site Settings
      </div>
      <div class="panel-body">
        <div var="form"></div>
      </div>
  </div>
</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }
}