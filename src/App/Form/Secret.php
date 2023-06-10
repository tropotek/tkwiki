<?php
namespace App\Form;

use App\Db\Page;
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


    public function __construct()
    {
        $this->setForm(Form::create('secret'));
    }

    public function doDefault(Request $request, int $id)
    {
        $this->secret = new \App\Db\Secret();

        if ($id) {
            $this->secret = \App\Db\SecretMap::create()->find($id);
            if (!$this->secret) {
                throw new Exception('Invalid ID: ' . $id);
            }
        }

        $list = UserMap::create()->findFiltered(['type' => \App\Db\User::TYPE_STAFF], Tool::create('name'));
        $this->getForm()->appendField(new Field\Select('userId', $list))->prependOption('-- Select --', '');

        /** @var Field\Select $permission */
        $this->getForm()->appendField(new Field\Select('permission', array_flip(Page::PERM_LIST)))
            ->setRequired()
            ->prependOption('-- Select --', '');

        $this->getForm()->appendField(new Field\Input('name'));
        $this->getForm()->appendField(new Field\Input('url'));
        $this->getForm()->appendField(new Field\Input('username'));
        $this->getForm()->appendField(new Field\Password('password'));
        $this->getForm()->appendField(new Field\Input('otp'));
        $this->getForm()->appendField(new Field\Textarea('keys'));
        $this->getForm()->appendField(new Field\Textarea('notes'));


        $this->getForm()->appendField(new Action\SubmitExit('save', [$this, 'onSubmit']));
        $this->getForm()->appendField(new Action\Link('cancel', Uri::create('/secretManager')));

        $load = $this->secret->getMapper()->getFormMap()->getArray($this->secret);
        $load['id'] = $this->secret->getId();
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
        $action->setRedirect(Uri::create());
        if ($form->getTriggeredAction()->isExit()) {
            $action->setRedirect(Uri::create('/secretManager'));
        }
    }

    public function show(): ?Template
    {
        // Setup field group widths with bootstrap classes
        $this->getForm()->getField('userId')->addFieldCss('col-sm-6');
        $this->getForm()->getField('permission')->addFieldCss('col-sm-6');
        $this->getForm()->getField('name')->addFieldCss('col-sm-6');
        $this->getForm()->getField('url')->addFieldCss('col-sm-6');
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

    public function setSecret($secret)
    {
        return $this->secret = $secret;
    }

}