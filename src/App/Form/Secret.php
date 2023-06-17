<?php
namespace App\Form;

use App\Db\UserMap;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Db\Tool;
use Tk\Exception;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Action;
use Tk\FormRenderer;
use Tk\Traits\SystemTrait;
use Tk\Uri;

class Secret
{
    use SystemTrait;
    use Form\FormTrait;

    protected ?\App\Db\Secret $secret = null;

    protected bool $htmx = false;

    public function __construct(bool $htmx = false)
    {
        $this->htmx = $htmx;
        $this->setForm(Form::create('secret'));
    }

    public function doDefault(Request $request, int $id)
    {
        $this->secret = new \App\Db\Secret();
        $this->secret->setUserId($this->getFactory()->getAuthUser()->getId());

        if ($id) {
            $this->secret = \App\Db\SecretMap::create()->find($id);
            if (!$this->secret) {
                throw new Exception('Invalid ID: ' . $id);
            }
        }

        // Enable HTMX
        if ($this->isHtmx()) {
            $this->getForm()->setAttr('hx-post', Uri::create());
            $this->getForm()->setAttr('hx-target', 'this');
            $this->getForm()->setAttr('hx-swap', 'outerHTML');
            $this->getForm()->setAttr('hx-select', '#'.$this->form->getId());
            // trigger JS init event on settle
            header('HX-Trigger-After-Settle: tk-init-form');
        }

        $tab = 'Details';
        $this->getForm()->appendField(new Field\Hidden('secret_id'));

//        $list = UserMap::create()->findFiltered(['type' => \App\Db\User::TYPE_STAFF], Tool::create('name'));
//        $this->getForm()->appendField(new Field\Select('userId', $list))
//            ->setGroup($tab)
//            ->prependOption('-- Select --', '');

        $this->getForm()->appendField(new Field\Input('name'))
            ->setGroup($tab);

        /** @var Field\Select $permission */
        $this->getForm()->appendField(new Field\Select('permission', array_flip(\App\Db\Secret::PERM_LIST)))
            ->setGroup($tab)
            ->setRequired()
            ->prependOption('-- Select --', '');

        $this->getForm()->appendField(new Field\Input('url'))
            ->setGroup($tab);

        $this->getForm()->appendField(new Field\Input('username'))
            ->setGroup($tab);

        $this->getForm()->appendField(new Field\Password('password'))
            ->setGroup($tab);

        $this->getForm()->appendField(new Field\Input('otp'))
            ->setGroup($tab)
            ->setNotes('OTP secret passphrase. Generate 6 number code based on passphrase. <a href="https://en.wikipedia.org/wiki/One-time_password" target="_blank">More here</a>');

        $tab = 'Extra';
        $this->getForm()->appendField(new Field\Textarea('keys'))
            ->setGroup($tab);

        $this->getForm()->appendField(new Field\Textarea('notes'))
            ->setGroup($tab);


        if ($this->isHtmx()) {
            $this->getForm()->appendField(new Action\Submit('insert', [$this, 'onSubmit']));
        } else {
            $this->getForm()->appendField(new Action\SubmitExit('save', [$this, 'onSubmit']));
        }
        $this->getForm()->appendField(new Action\Link('cancel', Uri::create('/secretManager')));

        $load = $this->secret->getMapper()->getFormMap()->getArray($this->secret);
        $load['secret_id'] = $this->secret->getId();
        $this->getForm()->setFieldValues($load); // Use form data mapper if loading objects

        $this->getForm()->execute($request->request->all());

        $this->setFormRenderer(new FormRenderer($this->getForm()));

    }

    public function onSubmit(Form $form, Action\ActionInterface $action)
    {
        $this->secret->getMapper()->getFormMap()->loadObject($this->secret, $form->getFieldValues());

        $form->addFieldErrors($this->secret->validate());
        if ($form->hasErrors()) {
            return;
        }

        $this->secret->save();

        Alert::addSuccess('Form save successfully.');
        $action->setRedirect(Uri::create()->set('id', $this->secret->getId()));
        if ($form->getTriggeredAction()->isExit()) {
            $action->setRedirect($this->getFactory()->getBackUrl());
            //$action->setRedirect(Uri::create('/secretManager'));
        }

        if ($this->isHtmx()) {
            $form->setFieldValue('secret_id', $this->getSecret()->getId());
            $action->setRedirect(null);
            //$this->getForm()->setAttr('hx-post', Uri::create()->set('secret_id', $this->secret->getId()));
        }
    }

    public function show(): ?Template
    {
        // Setup field group widths with bootstrap classes
        //$this->getForm()->getField('userId')->addFieldCss('col-sm-6');
        $this->getForm()->getField('permission')->addFieldCss('col-sm-6');
        $this->getForm()->getField('name')->addFieldCss('col-sm-6');
        //$this->getForm()->getField('url')->addFieldCss('col-sm-6');
        $this->getForm()->getField('username')->addFieldCss('col-sm-6');
        $this->getForm()->getField('password')->addFieldCss('col-sm-6');

        $renderer = $this->getFormRenderer();
        $renderer->addFieldCss('mb-3');

        return $renderer->show();
    }


    public function getSecret(): ?\App\Db\Secret
    {
        return $this->secret;
    }

    public function isHtmx(): bool
    {
        return $this->htmx;
    }

}