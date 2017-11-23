<?php
namespace App\Controller\Admin\User;

use Tk\Request;
use App\Controller\Iface;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form\Field\Option\ArrayObjectIterator;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends Iface
{

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \App\Db\User
     */
    private $user = null;


    /**
     *
     */
    public function __construct()
    {
        $title = 'User Edit';
        if ($this->isProfile()) {
            $title = 'My Profile';
        }
        parent::__construct($title);
    }

    /**
     * @return bool
     */
    public function isProfile() 
    {
        return  (\Tk\Uri::create()->getBasename() == 'profile.html');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        $this->user = new \App\Db\User();
        if ($this->isProfile()) {
            $this->user = $this->getUser();
        } else if ($request->get('userId')) {
            $this->user = \App\Db\UserMap::create()->find($request->get('userId'));
        }

        $this->form = Form::create('formEdit');

        $this->form->addField(new Field\Input('name'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('username'))->setRequired(true)->setTabGroup('Details');
        $emailF = $this->form->addField(new Field\Input('email'))->setRequired(true)->setTabGroup('Details');
        
        if ($this->isProfile()) {
            $emailF->setAttr('readonly', 'readonly');
        } else {
            $f = $this->form->addField(new Field\Checkbox('active'))->setTabGroup('Details');
            if ($this->user->id == 1) {
                $f->setAttr('disabled');
            }
        }   
        
        $this->form->setAttr('autocomplete', 'off');
        $f = $this->form->addField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setTabGroup('Password');
        if (!$this->user->getId())
            $f->setRequired(true);
        $f = $this->form->addField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setNotes('Change this users password.')->setTabGroup('Password');
        if (!$this->user->getId())
            $f->setRequired(true);

        
        if (!$this->isProfile()) {
            $roles = \App\Db\RoleMap::create()->findAll(\Tk\Db\Tool::create('a.id'))->toArray();

            $list = new ArrayObjectIterator($roles);
            $f = $this->form->addField(new Field\CheckboxGroup('role', $list))->setNotes('Select the access level for this user')->setRequired(true)->setTabGroup('Roles');
            if ($this->user->id == 1) {         // Disable for admin user.... Has all roles and cannot be revoked
                $f->setAttr('disabled');
            }

            $selected = array();
            foreach($this->user->getAcl()->getRoles() as $obj) {
                $selected[] = $obj->id;
            }
            $this->form->setFieldValue('role', $selected);

        }

        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\LinkButton('cancel', \Tk\Uri::create('userManager.html')));

        $this->form->load(\App\Db\UserMap::create()->unmapForm($this->user));
        $this->form->execute();


        return $this->show();
    }

    /**
     * @param \Tk\Form $form
     */
    public function doSubmit($form)
    {
        // Load the object with data from the form using a helper object
        //\App\Form\ModelLoader::loadObject($form, $this->user);
        \App\Db\UserMap::create()->mapForm($form->getValues(), $this->user);
        
        // Password validation needs to be here
        if ($this->form->getFieldValue('newPassword')) {
            if ($this->form->getFieldValue('newPassword') != $this->form->getFieldValue('confPassword')) {
                $form->addFieldError('newPassword', 'Passwords do not match.');
                $form->addFieldError('confPassword');
            }
        }
        $form->addFieldErrors($this->user->validate());
        
        if ($form->hasErrors()) {
            return;
        }
        
        if ($this->user->id == 1) {
            // Ensure admin is not disabled
            $this->user->active = true;
        }
        
        if ($this->form->getFieldValue('newPassword')) {
            $this->user->password = \App\Factory::hashPassword($this->form->getFieldValue('newPassword'));
        }

        $this->user->save();

        // Update user role list if not admin
        if (!$this->isProfile() && $this->user->id != 1) {
            \App\Db\RoleMap::create()->deleteAllUserRoles($this->user->id);
            foreach ($form->getFieldValue('role') as $roleId) {
                \App\Db\RoleMap::create()->addUserRole($roleId, $this->user->id);
            }
        }

        \Tk\Alert::addSuccess('User record saved!');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            if ($this->isProfile()) {
                \Tk\Uri::create('/')->redirect();
            }
            \Tk\Uri::create('/userManager.html')->redirect();
        }
        \Tk\Uri::create()->set('userId', $this->user->id)->redirect();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();
        
        if ($this->user->id)
            $template->insertText('username', $this->user->name . ' - [UID ' . $this->user->id . ']');
        else
            $template->insertText('username', 'Create User');
        
        // Render the form
        $fren = new \Tk\Form\Renderer\Dom($this->form);
        $template->insertTemplate($this->form->getId(), $fren->show());

        return $this->getPage()->setPageContent($this->getTemplate());
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