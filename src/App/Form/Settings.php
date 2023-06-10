<?php
namespace App\Form;

use App\Db\PageMap;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Db\Tool;
use Tk\Form\Field;
use Tk\Form;
use Tk\FormRenderer;
use Tk\Traits\SystemTrait;
use Tk\Uri;

class Settings
{
    use SystemTrait;
    use Form\FormTrait;

    public function __construct()
    {
        $this->setForm(Form::create('settings'));
    }

    public function doDefault(Request $request)
    {
        $tab = 'Site';
        $this->getForm()->appendField(new Field\Input('site.name'))
            ->setLabel('Site Title')
            ->setNotes('Site Full title. Used for email subjects and content texts.')
            ->setRequired(true)
            ->setGroup($tab);

        $this->getForm()->appendField(new Field\Input('site.name.short'))
            ->setGroup($tab)
            ->setLabel('Site Short Title')
            ->setNotes('Site short title. Used for nav bars and title where space is limited.')
            ->setRequired(true);

        $this->getForm()->appendField(new Field\Checkbox('site.account.registration'))
            ->setGroup($tab)
            ->setLabel('Account Registration')
            ->setNotes('Enable public user registrations for this site. (Default user type is `user`)')
            ->addOnShowOption(function (\Dom\Template $template, \Tk\Form\Field\Option $option, $var) {
                $option->setName('Enable');
            });

        $list = PageMap::create()->findFiltered([
            'permission' => \App\Db\Page::PERM_PUBLIC,
            'published'  => true], Tool::create('created', 25));
        $list = Field\Option\ArrayObjectIterator::create($list, 'title', 'url');
        $this->getForm()->appendField(new Field\Select('wiki.page.default', $list))
            ->setLabel('Home Page')
            ->setNotes('Select the default wiki page home content.<br/>Note you cannot delete a home page, you must reassign it first.')
            ->setRequired(true)
            ->addCss('select-home')
            ->setGroup($tab);

        $this->getForm()->appendField(new Field\Checkbox('wiki.enable.credential.mod'))
            ->setGroup($tab)
            ->setLabel('Enable Secure Credential Module')
            ->setNotes('Store passwords and secret keys and configure what users can view them in a page')
            ->addOnShowOption(function (\Dom\Template $template, \Tk\Form\Field\Option $option, $var) {
                $option->setName('Enable');
            });


        $tab = 'Email';
        $this->getForm()->appendField(new Field\Input('site.email'))
            ->setLabel('Site Email')
            ->setRequired(true)
            ->setNotes('The default sender address when sending system emails.')
            ->setGroup($tab);

        $this->getForm()->appendField(new Field\Textarea('site.email.sig'))
            ->setLabel('Email Signature')
            ->setNotes('Set the email signature to appear at the footer of all system emails.')
            ->addCss('mce-min')
            ->setGroup($tab);

        $tab = 'Metadata';
        $this->getForm()->appendField(new Field\Input('system.meta.keywords'))
            ->setLabel('Metadata Keywords')
            ->setNotes('Set meta tag SEO keywords for this site. ')
            ->setGroup($tab);

        $this->getForm()->appendField(new Field\Input('system.meta.description'))
            ->setLabel('Metadata Description')
            ->setNotes('Set meta tag SEO description for this site. ')
            ->setGroup($tab);

        $this->getForm()->appendField(new Field\Textarea('system.global.js'))
            ->setAttr('id', 'site-global-js')
            ->setLabel('Custom JS')
            ->setNotes('You can omit the &lt;script&gt; tags here')
            ->addCss('code')->setAttr('data-mode', 'javascript')
            ->setGroup($tab);

        $this->getForm()->appendField(new Field\Textarea('system.global.css'))
            ->setAttr('id', 'site-global-css')
            ->setLabel('Custom CSS Styles')
            ->setNotes('You can omit the &lt;style&gt; tags here')
            ->addCss('code')->setAttr('data-mode', 'css')
            ->setGroup($tab);

//        $tab = 'API Keys';
//        $this->getForm()->appendField(new Field\Input('google.map.apikey'))
//            ->setGroup($tab)->setLabel('Google API Key')
//            ->setNotes('<a href="https://cloud.google.com/maps-platform/" target="_blank">Get Google Maps Api Key</a> And be sure to enable `Maps Javascript API`, `Maps Embed API` and `Places API for Web` for this site.')
//            ->setGroup($tab);

        $tab = 'Maintenance';
        $this->getForm()->appendField(new Field\Checkbox('system.maintenance.enabled'))
            ->addCss('check-enable')
            ->setLabel('Maintenance Mode Enabled')
            ->setNotes('Enable maintenance mode. Admin users will still have access to the site.')
            ->setGroup($tab)
            ->addOnShowOption(function (\Dom\Template $template, \Tk\Form\Field\Option $option, $var) {
                $option->setName('Enable');
            });

        $this->getForm()->appendField(new Field\Textarea('system.maintenance.message'))
            ->addCss('mce-min')
            ->setLabel('Message')
            ->setNotes('Set the message public users will see when in maintenance mode.')
            ->setGroup($tab);


        $this->getForm()->appendField(new Form\Action\SubmitExit('save', [$this, 'onSubmit']));
        $this->getForm()->appendField(new Form\Action\Link('back', Uri::create('/')));

        $this->getForm()->setFieldValues($this->getRegistry()->all()); // Use form data mapper if loading objects

        // Replace converted key from request
        $values = array_combine(
            array_map(fn($r) => str_replace('_', '.', $r), array_keys($this->getRequest()->request->all()) ),
            array_values($this->getRequest()->request->all())
        );
        $this->getForm()->execute($values);

        $this->setFormRenderer(new FormRenderer($this->getForm()));

    }

    public function onSubmit(Form $form, Form\Action\ActionInterface $action)
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
            $action->setRedirect(Uri::create('/dashboard'));
        }
    }

    public function show(): ?Template
    {
        $renderer = $this->getFormRenderer();
        $this->getForm()->getField('site.name')->addFieldCss('col-6');
        $this->getForm()->getField('site.name.short')->addFieldCss('col-6');
        $renderer->addFieldCss('mb-3');

        return $renderer->show();
    }
}