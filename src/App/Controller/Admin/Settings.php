<?php
namespace App\Controller\Admin;

use App\Db\Page;
use Bs\ControllerPublic;
use Bs\Db\Permissions;
use Bs\Form;
use Dom\Template;
use Tk\Alert;
use Tk\Form\Action\Link;
use Tk\Form\Action\SubmitExit;
use Tk\Form\Field\Checkbox;
use Tk\Form\Field\Input;
use Tk\Form\Field\Option;
use Tk\Form\Field\Select;
use Tk\Form\Field\Textarea;
use Tk\Uri;
use Tt\DbFilter;

class Settings extends ControllerPublic
{

    protected ?Form  $form = null;

    public function doDefault(): void
    {
        $this->getPage()->setTitle('Edit Settings');
        $this->setAccess(Permissions::PERM_SYSADMIN);

        $this->getRegistry()->save();
        $this->getCrumbs()->reset();

        $this->form = new Form();


        $tab = 'Site';
        $this->form->appendField(new Input('site.name'))
            ->setGroup($tab)
            ->setLabel('Site Title')
            ->setNotes('Site Full title. Used for email subjects and content texts.')
            ->setRequired();

        $this->form->appendField(new Input('site.name.short'))
            ->setGroup($tab)
            ->setLabel('Site Short Title')
            ->setNotes('Site short title. Used for nav bars and title where space is limited.')
            ->setRequired();

        $this->form->appendField(new Checkbox('site.account.registration'))
            ->setGroup($tab)
            ->setLabel('Account Registration')
            ->setNotes('Enable public user registrations for this site. (Default user type is `user`)')
            ->addOnShowOption(function (Template $template, Option $option, $var) {
                $option->setName('Enable');
            });

        $list = Page::findFiltered(DbFilter::create([
            'permission' => Page::PERM_PUBLIC,
            'published'  => true
        ], 'created', 25));

        $this->form->appendField(new Select('wiki.page.default', $list, 'title', 'url'))
            ->setGroup($tab)
            ->setLabel('Home Page')
            ->setNotes('Select the default wiki page home content.<br/>Note: you cannot delete a home page, you must reassign it first.')
            ->setRequired()
            ->addCss('select-home');

        $this->form->appendField(new Checkbox('wiki.enable.secret.mod'))
            ->setGroup($tab)
            ->setLabel('Enable Secure Credential Module')
            ->setNotes('Store passwords and secret keys securely and with view/edit permissions')
            ->addOnShowOption(function (Template $template, Option $option, $var) {
                $option->setName('Enable');
            });

        $list = $this->getConfig()->get('wiki.templates', []);
        $this->form->appendField(new Select('wiki.default.template', $list))
            ->setGroup($tab)
            ->setRequired()
            ->setNotes('Select the sites default template');


        $tab = 'Email';
        $this->form->appendField(new Input('site.email'))
            ->setGroup($tab)
            ->setLabel('Site Email')
            ->setRequired()
            ->setNotes('The default sender address when sending system emails.');

        $this->form->appendField(new Textarea('site.email.sig'))
            ->setGroup($tab)
            ->setLabel('Email Signature')
            ->setNotes('Set the email signature to appear at the footer of all system emails.')
            ->addCss('mce-min');


        $tab = 'Metadata';
        $this->form->appendField(new Input('system.meta.keywords'))
            ->setGroup($tab)
            ->setLabel('Metadata Keywords')
            ->setNotes('Set meta tag SEO keywords for this site. ');

        $this->form->appendField(new Input('system.meta.description'))
            ->setGroup($tab)
            ->setLabel('Metadata Description')
            ->setNotes('Set meta tag SEO description for this site. ');

        $this->form->appendField(new Textarea('system.global.js'))
            ->setGroup($tab)
            ->setAttr('id', 'site-global-js')
            ->setLabel('Global JavaScript')
            ->setNotes('You can omit the &lt;script&gt; tags here')
            ->addCss('code')->setAttr('data-mode', 'javascript');

        $this->form->appendField(new Textarea('system.global.css'))
            ->setGroup($tab)
            ->setAttr('id', 'site-global-css')
            ->setLabel('Global CSS Styles')
            ->setNotes('You can omit the &lt;style&gt; tags here')
            ->addCss('code')->setAttr('data-mode', 'css');


        $tab = 'Maintenance';
        $this->form->appendField(new Checkbox('system.maintenance.enabled'))
            ->setGroup($tab)
            ->addCss('check-enable')
            ->setLabel('Maintenance Mode Enabled')
            ->setNotes('Enable maintenance mode. Admin users will still have access to the site.')
            ->addOnShowOption(function (Template $template, Option $option, $var) {
                $option->setName('Enable');
            });

        $this->form->appendField(new Textarea('system.maintenance.message'))
            ->setGroup($tab)
            ->addCss('mce-min')
            ->setLabel('Message')
            ->setNotes('Set the message public users will see when in maintenance mode.');

        $this->form->appendField(new SubmitExit('save', [$this, 'onSubmit']));
        $this->form->appendField(new Link('back', $this->getBackUrl()));


        // Load form with object values
        $this->form->setFieldValues($this->getRegistry()->all());

        // Execute form with request values
        $values = array_combine(
            array_map(fn($r) => str_replace('_', '.', $r), array_keys($_POST) ),
            array_values($_POST)
        ) + $_POST; // keep the original post values for the events

        $this->form->execute($values);
    }

    public function onSubmit(Form $form, SubmitExit $action): void
    {
        $values = $form->getFieldValues();
        $this->getRegistry()->replace($values);

        if (strlen($values['site.name'] ?? '') < 3) {
            $form->addFieldError('site.name', 'Please enter your name');
        }
        if (!filter_var($values['site.email'] ?? '', \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('site.email', 'Please enter a valid email address');
        }

        if ($form->hasErrors()) return;

        $this->getRegistry()->save();

        Alert::addSuccess('Site settings saved successfully.');
        $action->setRedirect(Uri::create());
        if ($form->getTriggeredAction()->isExit()) {
            $action->setRedirect($this->getBackUrl());
        }
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());
        $template->setAttr('back', 'href', $this->getBackUrl());

        $this->form->getField('site.name')->addFieldCss('col-6');
        $this->form->getField('site.name.short')->addFieldCss('col-6');
        $this->form->getRenderer()->addFieldCss('mb-3');

        $template->setVisible('staff', $this->getAuthUser()->hasPermission(Permissions::PERM_MANAGE_STAFF));
        $template->setVisible('member', $this->getAuthUser()->hasPermission(Permissions::PERM_MANAGE_MEMBERS));

        $template->appendTemplate('content', $this->form->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <div class="page-actions card mb-3">
    <div class="card-header"><i class="fa fa-cogs"></i> Actions</div>
    <div class="card-body" var="actions">
      <a href="/" title="Back" class="btn btn-outline-secondary" var="back"><i class="fa fa-arrow-left"></i> Back</a>
      <a href="/user/staffManager" title="Manage Staff" class="btn btn-outline-secondary"><i class="fa fa-fw fa-users" choice="staff"></i> Staff</a>
      <a href="/user/memberManager" title="Manage Members" class="btn btn-outline-secondary"><i class="fa fa-fw fa-users" choice="member"></i> Members</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-cogs"></i> </div>
    <div class="card-body" var="content"></div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}