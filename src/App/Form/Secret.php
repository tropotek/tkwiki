<?php
namespace App\Form;

use App\Component\QrcodeReader;
use Bs\Mvc\Form;
use Bs\Traits\SystemTrait;
use Dom\Template;
use Tk\Alert;
use Tk\Form\Action\Link;
use Tk\Form\Action\Submit;
use Tk\Form\Action\SubmitExit;
use Tk\Form\Field\Checkbox;
use Tk\Form\Field\Hidden;
use Tk\Form\Field\Input;
use Tk\Form\Field\InputButton;
use Tk\Form\Field\Password;
use Tk\Form\Field\Select;
use Tk\Form\Field\Textarea;
use Tk\Uri;

class Secret extends Form
{
    use SystemTrait;

    protected bool $htmx = false;


    public function init(): static
    {
        $tab = 'Details';
        $this->appendField(new Hidden('hash')); // needed for Htmx insert

        $this->appendField(new Input('name'))
            ->setGroup($tab);

        $this->appendField((new Select('permission', \App\Db\Secret::PERM_LIST))
            ->setGroup($tab)
            ->setStrict(true)
            ->setRequired()
            ->addFieldCss('col-sm-6')
            ->prependOption('-- Select --')
        );

        $this->appendField(new Input('url'))
            ->setGroup($tab)
            ->addFieldCss('col-sm-6');

        $this->appendField(new Input('username'))
            ->setGroup($tab)
            ->addFieldCss('col-sm-6');

        $this->appendField(new Password('password'))
            ->setGroup($tab)
            ->addFieldCss('col-sm-6');

//        $this->appendField(new Input('otp'))
//            ->setGroup($tab)
//            ->setNotes('OTP secret passphrase. Generate 6 number code based on passphrase. <a href="https://en.wikipedia.org/wiki/One-time_password" target="_blank">More here</a>');


        $this->form->appendField((new InputButton('otp', '<i class="fas fa-qrcode"></i>'))
            ->setBtnAttr([
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#'.QrcodeReader::CONTAINER_ID,
            ])
            ->addBtnCss('border-light-subtle')
            ->setGroup($tab)
            ->setNotes('OTP secret passphrase. Generate 6 number code based on passphrase. <a href="https://en.wikipedia.org/wiki/One-time_password" target="_blank">More here</a>')
        );

        $this->appendField(new Checkbox('publish', ['1' => 'Publish']))
            ->setLabel('')
            ->setGroup($tab);

        $tab = 'Extra';
        $this->appendField(new Textarea('keys'))
            ->setGroup($tab)
            ->setAttr('style', 'height: 20em;');

        $this->appendField(new Textarea('notes'))
            ->setGroup($tab)
            ->setAttr('style', 'height: 20em;');


        if ($this->isHtmx()) {
            $this->appendField(new Submit('insert', [$this, 'onSubmit']));
        } else {
            $this->appendField(new SubmitExit('save', [$this, 'onSubmit']));
        }
        $this->appendField(new Link('cancel', Uri::create($this->getBackUrl())));

        return $this;
    }

    public function execute(array $values = []): static
    {
        $this->init();

        $load = $this->getSecret()->unmapForm();
        $this->setFieldValues($load);

        return parent::execute($values);
    }

    public function onSubmit(Form $form, Submit $action): void
    {
        $this->getSecret()->mapForm($form->getFieldValues());

        $form->addFieldErrors($this->getSecret()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $this->getSecret()->save();

        Alert::addSuccess('Form save successfully.');
        $action->setRedirect(Uri::create()->set('h', $this->getSecret()->hash));
        if ($form->getTriggeredAction()->isExit()) {
            $action->setRedirect($this->getBackUrl());
        }

        if ($this->isHtmx()) {
            $form->setFieldValue('hash', $this->getSecret()->hash);
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