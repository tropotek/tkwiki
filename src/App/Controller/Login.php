<?php
namespace App\Controller;

use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Auth;
use Tk\Auth\Result;


/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Login extends Iface
{

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \Tk\EventDispatcher\EventDispatcher
     */
    private $dispatcher = null;
    

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct('Login');
        $this->dispatcher = $this->getConfig()->getEventDispatcher();
    }
    
    /**
     *
     * @param Request $request
     * @return Template
     */
    public function doDefault(Request $request)
    {
        if ($this->getUser()) {
            \Tk\Uri::create($this->getUser()->getHomeUrl())->redirect();
        }

        $this->form = new Form('loginForm');

        $this->form->addField(new Field\Input('username'));
        $this->form->addField(new Field\Password('password'));
        $this->form->addField(new Event\Button('login', array($this, 'doLogin')));
        
        // Find and Fire submit event
        $this->form->execute();

        return $this->show();
    }

    /**
     * show()
     *
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        // Render the form
        $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
        $ren->show();
        
        return $this->getPage()->setPageContent($template);
    }


    /**
     * doLogin()
     *
     * @param \Tk\Form $form
     * @throws \Tk\Exception
     */
    public function doLogin($form)
    {
        /** @var Auth $auth */
        $auth = \App\Factory::getAuth();

        if (!$form->getFieldValue('username') || !preg_match('/[a-z0-9_ -]{4,32}/i', $form->getFieldValue('username'))) {
            $form->addFieldError('username', 'Please enter a valid username');
        }
        if (!$form->getFieldValue('password') || !preg_match('/[a-z0-9_ -]{4,32}/i', $form->getFieldValue('password'))) {
            $form->addFieldError('password', 'Please enter a valid password');
        }
        
        if ($form->hasErrors()) {
            return;
        }

        // Fire the login event to allow developing of misc auth plugins
        $event = new \App\Event\AuthEvent($auth);
        $event->replace($form->getValues());
        $this->dispatcher->dispatch('auth.onLogin', $event);
        
        // Use the event to process the login like below....
        $result = $event->getResult();
        
        if (!$result) {
            $form->addError('Invalid login details');
            //$form->addError('No valid authentication result received.');
            return;
        }
        if ($result->getCode() == Result::SUCCESS) {
            // Redirect based on role
            \Tk\Uri::create($this->getUser()->getHomeUrl())->redirect();
        }
        $form->addError( implode("<br/>\n", $result->getMessages()) );
        return;
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>

  <div class="section">
    <div class="container">
      <div class="row">
        <div class="col-sm-5">
          <div class="basic-login clearfix">
            <div class="alert alert-danger" role="alert" choice="error"><strong>Error!</strong> <span var="errMsg">Invalid login credentials</span></div>
            <form role="form" id="loginForm" method="post">
              <input type="hidden" name="act" value="login" />

              <div class="form-group" var="username-group">
                <label for="login-username"><i class="icon-user"></i> <b>Username or Email</b></label>
                <input class="form-control" name="username" id="login-username" type="text" placeholder="" />
              </div>
              <div class="form-group" var="password-group">
                <label for="login-password"><i class="icon-lock"></i> <b>Password</b></label>
                <input class="form-control" name="password" id="login-password" type="password" placeholder="" />
              </div>
              <div class="form-group">
                <!-- label class="checkbox" for="login-remember">
                  <input type="checkbox" name="remember" value="remember" id="login-remember" /> Remember me
                </label -->
                <a href="/recover.html" class="forgot-password">Forgot password?</a>
                <button type="submit" name="login" class="btn pull-right">Login</button>
              </div>
            </form>
          </div>
        </div>
        <div class="col-sm-7 social-login clearfix">
          <p>Or login with your Facebook or Twitter</p>
          <div class="social-login-buttons">
            <a href="#" class="btn-facebook-login">Login with Facebook</a>
            <a href="#" class="btn-twitter-login">Login with Twitter</a>
          </div>
          <div class="not-member">
            <p>Not a member? <a href="/register.html">Register here</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}