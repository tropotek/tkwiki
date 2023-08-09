<?php
namespace App\Form;

use App\Db\PageMap;
use Bs\Form\EditInterface;
use Dom\Template;
use Tk\Alert;
use Tk\Db\Tool;
use Tk\Form\Field;
use Tk\Form;
use Tk\Uri;

class Settings extends EditInterface
{

    protected function initFields(): void
    {
        $tab = 'Site';
        $this->appendField(new Field\Input('site.name'))
            ->setGroup($tab)
            ->setLabel('Site Title')
            ->setNotes('Site Full title. Used for email subjects and content texts.')
            ->setRequired();

        $this->appendField(new Field\Input('site.name.short'))
            ->setGroup($tab)
            ->setLabel('Site Short Title')
            ->setNotes('Site short title. Used for nav bars and title where space is limited.')
            ->setRequired();

        $this->appendField(new Field\Checkbox('site.account.registration'))
            ->setGroup($tab)
            ->setLabel('Account Registration')
            ->setNotes('Enable public user registrations for this site. (Default user type is `user`)')
            ->addOnShowOption(function (\Dom\Template $template, \Tk\Form\Field\Option $option, $var) {
                $option->setName('Enable');
            });

        $list = PageMap::create()->findFiltered([
            'permission' => \App\Db\Page::PERM_PUBLIC,
            'published'  => true], Tool::create('created', 25));
        $this->appendField(new Field\Select('wiki.page.default', $list, 'title', 'url'))
            ->setGroup($tab)
            ->setLabel('Home Page')
            ->setNotes('Select the default wiki page home content.<br/>Note: you cannot delete a home page, you must reassign it first.')
            ->setRequired()
            ->addCss('select-home');

        $this->appendField(new Field\Checkbox('wiki.enable.secret.mod'))
            ->setGroup($tab)
            ->setLabel('Enable Secure Credential Module')
            ->setNotes('Store passwords and secret keys securely and with view/edit permissions')
            ->addOnShowOption(function (\Dom\Template $template, \Tk\Form\Field\Option $option, $var) {
                $option->setName('Enable');
            });

        $list = $this->getConfig()->get('wiki.templates', []);
        vd($list);
        $this->getForm()->appendField(new Field\Select('wiki.default.template', $list))
            ->setGroup($tab)
            ->setRequired()
            ->setNotes('Select the sites default template');


        $tab = 'Email';
        $this->appendField(new Field\Input('site.email'))
            ->setGroup($tab)
            ->setLabel('Site Email')
            ->setRequired()
            ->setNotes('The default sender address when sending system emails.');

        $this->appendField(new Field\Textarea('site.email.sig'))
            ->setGroup($tab)
            ->setLabel('Email Signature')
            ->setNotes('Set the email signature to appear at the footer of all system emails.')
            ->addCss('mce-min');


        $tab = 'Metadata';
        $this->appendField(new Field\Input('system.meta.keywords'))
            ->setGroup($tab)
            ->setLabel('Metadata Keywords')
            ->setNotes('Set meta tag SEO keywords for this site. ');

        $this->appendField(new Field\Input('system.meta.description'))
            ->setGroup($tab)
            ->setLabel('Metadata Description')
            ->setNotes('Set meta tag SEO description for this site. ');

        $this->appendField(new Field\Textarea('system.global.js'))
            ->setGroup($tab)
            ->setAttr('id', 'site-global-js')
            ->setLabel('Global JavaScript')
            ->setNotes('You can omit the &lt;script&gt; tags here')
            ->addCss('code')->setAttr('data-mode', 'javascript');

        $this->appendField(new Field\Textarea('system.global.css'))
            ->setGroup($tab)
            ->setAttr('id', 'site-global-css')
            ->setLabel('Global CSS Styles')
            ->setNotes('You can omit the &lt;style&gt; tags here')
            ->addCss('code')->setAttr('data-mode', 'css');

//        $tab = 'API Keys';
//        $this->appendField(new Field\Input('google.map.apikey'))
//            ->setGroup($tab)
//            ->setGroup($tab)->setLabel('Google API Key')
//            ->setNotes('<a href="https://cloud.google.com/maps-platform/" target="_blank">Get Google Maps Api Key</a> And be sure to enable `Maps Javascript API`, `Maps Embed API` and `Places API for Web` for this site.');


        $tab = 'Maintenance';
        $this->appendField(new Field\Checkbox('system.maintenance.enabled'))
            ->setGroup($tab)
            ->addCss('check-enable')
            ->setLabel('Maintenance Mode Enabled')
            ->setNotes('Enable maintenance mode. Admin users will still have access to the site.')
            ->addOnShowOption(function (\Dom\Template $template, \Tk\Form\Field\Option $option, $var) {
                $option->setName('Enable');
            });

        $this->appendField(new Field\Textarea('system.maintenance.message'))
            ->setGroup($tab)
            ->addCss('mce-min')
            ->setLabel('Message')
            ->setNotes('Set the message public users will see when in maintenance mode.');

        $this->appendField(new Form\Action\SubmitExit('save', [$this, 'onSubmit']));
        $this->appendField(new Form\Action\Link('back', $this->getBackUrl()));
    }

    public function execute(array $values = []): static
    {
        $this->setFieldValues($this->getRegistry()->all());
        $values = array_combine(
            array_map(fn($r) => str_replace('_', '.', $r), array_keys($this->getRequest()->request->all()) ),
            array_values($this->getRequest()->request->all())
        );
        return parent::execute($values);
    }

    public function onSubmit(Form $form, Form\Action\ActionInterface $action): void
    {
        $values = $form->getFieldValues();
        $this->getRegistry()->replace($values);

        if (strlen($values['site.name'] ?? '') < 3) {
            $form->addFieldError('site.name', 'Please enter your name');
        }
        if (!filter_var($values['site.email'] ?? '', \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('site.email', 'Please enter a valid email address');
        }

//        if (empty($values['wiki.page.default'])) {
//            $form->addFieldError('wiki.page.default', 'You must select a valid homepage');
//        }

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
        $renderer = $this->getFormRenderer();
        $this->getField('site.name')->addFieldCss('col-6');
        $this->getField('site.name.short')->addFieldCss('col-6');
        $renderer->addFieldCss('mb-3');

        return $renderer->show();
    }
}