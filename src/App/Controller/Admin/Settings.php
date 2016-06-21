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
        parent::__construct('WIKI Settings');
        $this->data = new \App\Db\Data();
        $this->data->loadData();
    }

    /**
     * doDefault
     *
     * @param Request $request
     * @return \App\Page\PublicPage
     */
    public function doDefault(Request $request)
    {

        $this->form = new Form('formEdit');

        $this->form->addField(new Field\Input('site.title'))->setLabel('Site Title')->setRequired(true);
        $this->form->addField(new Field\Input('site.email'))->setLabel('Site Email')->setRequired(true);
        
        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Link('cancel', \Tk\Uri::create('/index.html')));


        //$this->form->load($this->data->toArray());
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

        /** @var Field\File $attach */
        $attach = $form->getField('attach');

        if (empty($values['name'])) {
            $form->addFieldError('name', 'Please enter your name');
        }
        if (empty($values['email']) || !filter_var($values['email'], \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('email', 'Please enter a valid email address');
        }
        if (empty($values['message'])) {
            $form->addFieldError('message', 'Please enter some message text');
        }

        //$form->addFieldError('test', 'ggggg');
        
        // validate any files
        $attach->isValid();

        if ($this->form->hasErrors()) {
            return;
        }
        if ($attach->hasFile()) {
            $attach->moveUploadedFile($this->getConfig()->getDataPath() . '/contact/' . date('d-m-Y') . '-' . str_replace('@', '_', $values['email']));
        }

//        if ($this->sendEmail($form)) {
//            \App\Alert::getInstance()->addSuccess('<strong>Success!</strong> Your form has been sent.');
//        }

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
<div class="row">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-user fa-fw"></i>
        <span var="username"></span>
      </div>
      <!-- /.panel-heading -->
      <div class="panel-body ">
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