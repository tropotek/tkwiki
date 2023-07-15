<?php
namespace App\Form;

use Bs\Form\EditInterface;
use Dom\Template;
use Tk\Alert;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Action;
use Tk\Uri;

class Secret extends EditInterface
{

    protected bool $htmx = false;


    protected function initFields(): void
    {
        // Enable HTMX
        if ($this->isHtmx()) {
            $this->setAttr('hx-post', Uri::create());
            $this->setAttr('hx-target', 'this');
            $this->setAttr('hx-swap', 'outerHTML');
            $this->setAttr('hx-select', '#'.$this->form->getId());
            // trigger JS init event on settle
            header('HX-Trigger-After-Settle: tk-init-form');
        }

        $tab = 'Details';
        $this->appendField(new Field\Hidden('secretId'));

        $this->appendField(new Field\Input('name'))
            ->setGroup($tab);

        /** @var Field\Select $permission */
        $this->appendField(new Field\Select('permission', array_flip(\App\Db\Secret::PERM_LIST)))
            ->setGroup($tab)
            ->setRequired()
            ->prependOption('-- Select --', '');

        $this->appendField(new Field\Input('url'))
            ->setGroup($tab);

        $this->appendField(new Field\Input('username'))
            ->setGroup($tab);

        $this->appendField(new Field\Password('password'))
            ->setGroup($tab);

        $this->appendField(new Field\Input('otp'))
            ->setGroup($tab)
            ->setNotes('OTP secret passphrase. Generate 6 number code based on passphrase. <a href="https://en.wikipedia.org/wiki/One-time_password" target="_blank">More here</a>');


        $tab = 'Extra';
        $this->appendField(new Field\Textarea('keys'))
            ->setGroup($tab);

        $this->appendField(new Field\Textarea('notes'))
            ->setGroup($tab);


        if ($this->isHtmx()) {
            $this->appendField(new Action\Submit('insert', [$this, 'onSubmit']));
        } else {
            $this->appendField(new Action\SubmitExit('save', [$this, 'onSubmit']));
        }
        $this->appendField(new Action\Link('cancel', Uri::create($this->getFactory()->getBackUrl())));

    }

    public function execute(array $values = []): static
    {
        $load = $this->getSecret()->getMapper()->getFormMap()->getArray($this->getSecret());
        $load['secretId'] = $this->getSecret()->getSecretId();
        $this->getForm()->setFieldValues($load);
        parent::execute($values);
        return $this;
    }

    public function onSubmit(Form $form, Action\ActionInterface $action): void
    {
        $this->getSecret()->getMapper()->getFormMap()->loadObject($this->getSecret(), $form->getFieldValues());

        $form->addFieldErrors($this->getSecret()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $this->getSecret()->save();

        Alert::addSuccess('Form save successfully.');
        $action->setRedirect(Uri::create()->set('secretId', $this->getSecret()->getSecretId()));
        if ($form->getTriggeredAction()->isExit()) {
            $action->setRedirect($this->getFactory()->getBackUrl());
        }

        if ($this->isHtmx()) {
            $form->setFieldValue('secretId', $this->getSecret()->getSecretId());
            $action->setRedirect(null);
            //$this->setAttr('hx-post', Uri::create()->set('secretId', $this->getSecret()->getSecretId()));
        }
    }

    public function show(): ?Template
    {
        // Setup field group widths with bootstrap classes
        $this->getField('permission')->addFieldCss('col-sm-6');
        $this->getField('name')->addFieldCss('col-sm-6');
        $this->getField('username')->addFieldCss('col-sm-6');
        $this->getField('password')->addFieldCss('col-sm-6');
        $this->getField('keys')->setAttr('style', 'height: 20em;');
        $this->getField('notes')->setAttr('style', 'height: 20em;');

        $renderer = $this->getFormRenderer();
        $renderer->addFieldCss('mb-3');

        return $renderer->show();
    }


    public function getSecret(): ?\App\Db\Secret
    {
        return $this->getModel();
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