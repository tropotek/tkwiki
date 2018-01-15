<?php
namespace App\Controller;

use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Request;
use Tk\Auth\AuthEvents;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Register extends Iface
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
     * @param Request $request
     * @throws \Exception
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Create New Account');

        if (!$this->getConfig()->get('site.user.registration')) {
            \Tk\Alert::addError('User registration has been disabled on this site.');
            \Tk\Uri::create('/')->redirect();
        }
        if ($request->has('h')) {
            $this->doConfirmation($request);
        }
        if ($this->getUser()) {
            \Tk\Uri::create($this->getUser()->getHomeUrl())->redirect();
        }

        $this->user = new \App\Db\User();
        $this->user->role = \App\Auth\Acl::ROLE_USER;

        $this->form = new Form('registerForm', $request);

        $this->form->addField(new Field\Input('name'));
        $this->form->addField(new Field\Input('email'));
        $this->form->addField(new Field\Input('username'));
        $this->form->addField(new Field\Password('password'));
        $this->form->addField(new Field\Password('passwordConf'));
        $this->form->addField(new Event\Button('login', array($this, 'doRegister')));

        $this->form->load(\App\Db\UserMap::create()->unmapForm($this->user));
        
        // Find and Fire submit event
        $this->form->execute();

    }


    /**
     * doLogin()
     *
     * @param \Tk\Form $form
     */
    public function doRegister($form)
    {
        \App\Db\UserMap::create()->mapForm($form->getValues(), $this->user);

        if (!$this->form->getFieldValue('password')) {
            $form->addFieldError('password', 'Please enter a password');
            $form->addFieldError('passwordConf');
        }
        // Check the password strength, etc....
        if (!preg_match('/.{6,32}/', $this->form->getFieldValue('password'))) {
            $form->addFieldError('password', 'Please enter a valid password');
            $form->addFieldError('passwordConf');
        }
        // Password validation needs to be here
        if ($this->form->getFieldValue('password') != $this->form->getFieldValue('passwordConf')) {
            $form->addFieldError('password', 'Passwords do not match.');
            $form->addFieldError('passwordConf');
        }

        $form->addFieldErrors($this->user->validate());

        if ($form->hasErrors()) {
            return;
        }

        // Create a user and make a temp hash until the user activates the account
        $hash = $this->user->generateHash(true);
        $this->user->hash = $hash;
        $this->user->active = false;
        $this->user->password = $this->getConfig()->hashPassword($this->user->password);
        
        $this->user->save();

        // Fire the login event to allow developing of misc auth plugins
        $event = new \Tk\Event\Event();
        $event->set('form', $form);
        $event->set('user', $this->user);
        $this->getConfig()->getEventDispatcher()->dispatch(AuthEvents::REGISTER, $event);
        
        // Redirect with message to check their email
        \Tk\Alert::addSuccess('Your New Account Has Been Created. Check your email to activate the account.');
        \Tk\Config::getInstance()->getSession()->set('h', $this->user->hash);
        \Tk\Uri::create()->redirect();
    }

    /**
     * Activate the user account if not activated already, then trash the request hash....
     *
     * @param Request $request
     */
    public function doConfirmation($request)
    {
        // Receive a users on confirmation and activate the user account.
        $hash = $request->get('h');
        if (!$hash) {
            throw new \InvalidArgumentException('Cannot locate user. Please contact administrator.');
        }
        /** @var \App\Db\User $user */
        $user = \App\Db\UserMap::create()->findByHash($hash);
        if (!$user) {
            throw new \InvalidArgumentException('Cannot locate user. Please contact administrator.');
        }
        $user->hash = $user->generateHash();
        $user->active = true;
        $user->save();
        
        $event = new \Tk\Event\RequestEvent($request);
        $event->set('user', $user);
        $this->getConfig()->getEventDispatcher()->dispatch('auth.onRegisterConfirm', $event);
        
        \Tk\Alert::addSuccess('Account Activation Successful.');
        \Tk\Uri::create('/login.html')->redirect();
        
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        if ($this->getConfig()->getSession()->getOnce('h')) {
            $template->setChoice('success');
            
        } else {
            $template->setChoice('form');
            // Render the form
            $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
            $ren->show();
        }
        
        return $template;
    }

}