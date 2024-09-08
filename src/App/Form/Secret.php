<?php
namespace App\Form;

use Bs\Form;
use Dom\Template;
use Tk\Alert;
use Tk\Form\Action\Link;
use Tk\Form\Action\Submit;
use Tk\Form\Action\SubmitExit;
use Tk\Form\Field\Hidden;
use Tk\Form\Field\Input;
use Tk\Form\Field\Password;
use Tk\Form\Field\Select;
use Tk\Form\Field\Textarea;
use Tk\Uri;

class Secret extends Form
{
    protected bool $htmx = false;


    public function init(): static
    {

        $tab = 'Details';
        $this->appendField(new Hidden('secretId'));

        $this->appendField(new Input('name'))
            ->setGroup($tab);

        /** @var Select $permission */
        $this->appendField(new Select('permission', array_flip(\App\Db\Secret::PERM_LIST)))
            ->setGroup($tab)
            ->setRequired()
            ->prependOption('-- Select --', '');

        $this->appendField(new Input('url'))
            ->setGroup($tab);

        $this->appendField(new Input('username'))
            ->setGroup($tab);

        $this->appendField(new Password('password'))
            ->setGroup($tab);

        $this->appendField(new Input('otp'))
            ->setGroup($tab)
            ->setNotes('OTP secret passphrase. Generate 6 number code based on passphrase. <a href="https://en.wikipedia.org/wiki/One-time_password" target="_blank">More here</a>');


        $tab = 'Extra';
        $this->appendField(new Textarea('keys'))
            ->setGroup($tab);

        $this->appendField(new Textarea('notes'))
            ->setGroup($tab);


        if ($this->isHtmx()) {
            $this->appendField(new Submit('insert', [$this, 'onSubmit']));
        } else {
            $this->appendField(new SubmitExit('save', [$this, 'onSubmit']));
        }
        $this->appendField(new Link('cancel', Uri::create($this->getFactory()->getBackUrl())));

        return $this;
    }

    public function execute(array $values = []): static
    {
        $this->init();

        $load = $this->unmapValues($this->getSecret());
        $load['secretId'] = $this->getSecret()->secretId;
        $this->setFieldValues($load);

        return parent::execute($values);
    }

    public function onSubmit(Form $form, Submit $action): void
    {
        $form->mapValues($this->getSecret());

        $form->addFieldErrors($this->getSecret()->validate());
        vd($form->hasErrors(), $form->getAllErrors());
        if ($form->hasErrors()) {
            return;
        }

        $this->getSecret()->save();

        Alert::addSuccess('Form save successfully.');
        $action->setRedirect(Uri::create()->set('secretId', $this->getSecret()->secretId));
        if ($form->getTriggeredAction()->isExit()) {
            $action->setRedirect($this->getFactory()->getBackUrl());
        }

        if ($this->isHtmx()) {
            $form->setFieldValue('secretId', $this->getSecret()->secretId);
            $action->setRedirect(null);
            header('HX-Trigger-After-Settle: secret-success');
        }
    }

    public function show(): ?Template
    {
        // Enable HTMX
        if ($this->isHtmx()) {
            $this->setAttr('hx-post', Uri::create());
            $this->setAttr('hx-target', 'this');
            $this->setAttr('hx-swap', 'outerHTML');
            $this->setAttr('hx-select', '#'.$this->form->getId());
            $this->removeAttr('action');
        }

        // Setup field group widths with bootstrap classes
        $this->getField('permission')->addFieldCss('col-sm-6');
        $this->getField('name')->addFieldCss('col-sm-6');
        $this->getField('username')->addFieldCss('col-sm-6');
        $this->getField('password')->addFieldCss('col-sm-6');
        $this->getField('keys')->setAttr('style', 'height: 20em;');
        $this->getField('notes')->setAttr('style', 'height: 20em;');

        $renderer = $this->getRenderer();
        $renderer?->addFieldCss('mb-3');

        return $renderer?->show();
    }


    public function getSecret(): ?\App\Db\Secret
    {
        /** @var \App\Db\Secret $obj */
        $obj = $this->getModel();
        return $obj;
    }

    public function isHtmx(): bool
    {
        return $this->htmx;
    }

    public function setHtmx(bool $htmx): Secret
    {
        $this->htmx = $htmx;
        return $this;
    }

}