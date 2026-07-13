<?php
namespace App\Controller\User;

use App\Db\User;
use Bs\Auth;
use Bs\Db\Masquerade;
use Bs\Mvc\ControllerAdmin;
use Bs\Mvc\Form;
use Bs\Ui\Breadcrumbs;
use Dom\Template;
use Tk\Alert;
use Tk\Collection;
use Tk\Config;
use Tk\Form\Action\Link;
use Tk\Form\Action\SubmitExit;
use Tk\Form\Field\Checkbox;
use Tk\Form\Field\Hidden;
use Tk\Form\Field\Input;
use Tk\Form\Field\Password;
use Tk\Form\Field\Select;
use Tk\Uri;

/**
 *
 */
class Profile extends ControllerAdmin
{

    protected ?Form $form = null;
    protected ?User $user = null;


    public function doDefault(): void
    {
        $this->getPage()->setTitle('My Profile');

        if (!Auth::getAuthUser()) {
            Alert::addError('You do not have access to this page.');
            Uri::create('/')->redirect();
        }

        // Get the form template
        $this->user = User::getAuthUser();
        $this->form = new Form($this->user);

        // send inactive user activation email
        if (isset($_GET['pass'])) {
            $nonce = \Tk\Session::instance()->get('profile.pass.nonce', '');
            if (empty($nonce) || !hash_equals($nonce, (string)$_GET['pass'])) {
                throw new \Exception('Invalid user action, please contact your administrator.');
            }
            \Tk\Session::instance()->remove('profile.pass.nonce');
            if (\App\Email\User::sendRecovery($this->user)) {
                Alert::addSuccess('An email has been sent to ' . $this->user->nameShort . ' to create their password.');
            } else {
                Alert::addError('Failed to send email to ' . $this->user->nameShort . ' to create their password.');
            }
            Uri::create()->remove('pass')->redirect();
        }

        $tab = 'Details';
        $this->form->appendField(new Hidden('userId'))->setReadonly();

        $list = Collection::listCombine(User::TITLE_LIST);
        $this->form->appendField((new Select('title', $list))
            ->setGroup($tab)
            ->setLabel('Title')
            ->prependOption('')
        );

        $this->form->appendField(new Input('givenName'))
            ->setGroup($tab)
            ->setRequired();

        $this->form->appendField(new Input('familyName'))
            ->setGroup($tab);

        $this->form->appendField(new Input('username'))->setGroup($tab)
            ->setDisabled()
            ->setReadonly()
            ->setRequired();

        $this->form->appendField(new Input('email'))->setGroup($tab)
            ->setDisabled()
            ->setReadonly()
            ->setRequired();

        if ($this->user->isType(User::TYPE_STAFF)) {
            $list = User::PERMISSION_LIST;
            $this->form->appendField(new Checkbox('perm', $list))
                ->setGroup('Permissions')
                ->setDisabled()
                ->setReadonly();
        }

        $this->form->appendField(new SubmitExit('save', [$this, 'onSubmit']));
        $this->form->appendField(new Link('cancel', Breadcrumbs::getBackUrl()));

        // Load form with object values
        $load = $this->user->unmapForm();
        $load['perm'] = array_keys(
            array_filter(
                User::PERMISSION_LIST,
                fn($k) => ($k & $this->user->getAuth()->permissions) != 0,
                ARRAY_FILTER_USE_KEY
            )
        );
        $this->form->setFieldValues($load);

        $this->form->execute($_POST);
    }

    public function onSubmit(Form $form, SubmitExit $action): void
    {
        // set object values from fields
        $this->user->mapForm($form->getFieldValues());
        $this->user->getAuth()->mapForm($form->getFieldValues());

        $form->addFieldErrors($this->user->validate());
        $form->addFieldErrors($this->user->getAuth()->validate());

        if ($form->hasErrors()) {
            Alert::addError('Form contains errors.');
            return;
        }

        $this->user->save();
        $this->user->getAuth()->save();

        Alert::addSuccess('Form save successfully.');
        $action->setRedirect(Uri::create('/profile'));
        if ($form->getTriggeredAction()->isExit()) {
            $action->setRedirect(Uri::create('/'));
        }
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());
        $template->setAttr('back', 'href', Breadcrumbs::getBackUrl());

        $this->form->getField('title')->addFieldCss('col-1');
        $this->form->getField('givenName')->addFieldCss('col-5');
        $this->form->getField('familyName')->addFieldCss('col-6');

        $this->form->getField('username')->addFieldCss('col-6');
        $this->form->getField('email')->addFieldCss('col-6');
        $this->form->getRenderer()->addFieldCss('mb-3');
        $template->appendTemplate('content', $this->form->show());

        if (Config::instance()->get('auth.profile.password')) {
            $template->setVisible('pass');
            $nonce = bin2hex(random_bytes(16));
            \Tk\Session::instance()->set('profile.pass.nonce', $nonce);
            $url = Uri::create()->set('pass', $nonce);
            $template->setAttr('pass', 'href', $url);
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <div class="card mb-3">
    <div class="card-header"><i class="fa fa-cogs"></i> Actions</div>
    <div class="card-body" var="actions">
      <a href="/" title="Back" class="btn btn-outline-secondary" var="back"><i class="fa fa-arrow-left"></i> Back</a>
      <a href="/" title="Send Change Password Email" data-confirm="Request change password email?" class="btn btn-outline-secondary" choice="pass"><i class="fa fa-fw fa-key"></i> Change Password</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-user"></i> </div>
    <div class="card-body" var="content"></div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}