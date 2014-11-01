<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Com_Auth_Login
 *
 * @package Com
 */
class Com_Auth_Login extends Com_Web_Component
{
    /**
     * @var boolean
     */
    protected $remember = false;

    protected $recoverUrl = null;

    protected $registerUrl = null;




    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->addEvent('logout', 'doLogout');
    }

    /**
     * __makeTemplate
     *
     * @return Com_Web_Template
     */
    function __makeTemplate()
    {
        $html = '<?xml version="1.0"?>
<div class="Login">
  <h3>Login</h3>
  <p choice="logout"  class="notice logout">
    You are currently logged in as `<strong var="loggedUsername">----</strong>`, you can return <a href="#" var="userBtn" title="Return to home page">to your account page</a>,
    <a href="#" var="logoutBtn" title="Logout">logout</a> or you can use the form to login as another user.
  </p>
  <p choice="login" class="note">
    Enter your username and password to access this site. If you have unintentionally landed on this page you can return to the <a href="/index.html" title="Home Page">home page</a>.
  </p>
  <p class="capsOn" style="display: none;"><em>Warning:</em> Your `Caps Lock` key is on!</p>
  <div var="_Login"></div>

</div>';
        return Com_Web_Template::load($html);
    }


    /**
     * Add the default login event
     * Override to add a custom login form event
     *
     * @param unknown_type $form
     */
    function addLoginEvent($form)
    {
        $form->addEvent(Com_Form_Event_Login::create());
    }


    /**
     * init
     *
     */
    function init()
    {
        $form = Form::create('_Login');
        $this->addLoginEvent($form);

        $form->addField(Form_Field_Text::create('username'))->setRequired(true);
        $form->addField(Form_Field_Password::create('password'))->setRequired(true);
        if ($this->remember) {
            $form->addField(Form_Field_Checkbox::create('remember'))->setValue('remember', true);
        }

        $html = '';
        if ($this->registerUrl) {
            $html .= '<a href="' . htmlentities($this->registerUrl->toString()) . '" title="Register Now!">Register Now</a>';
            if ($this->recoverUrl) {
                $html .= " | ";
            }
        }
        if ($this->recoverUrl) {
            $html .= '<a href="' . htmlentities($this->recoverUrl->toString()) . '" title="Recover Password">Recover Password</a>';
        }
        if ($html) {
            $form->addField(Form_Field_Html::create('links', $html))->setLabel('');
        }
        $this->setForm($form);
    }

    /**
     * doLogout
     *
     */
    function doLogout()
    {
        $this->getAuth()->setUser(null);
        $this->logoutCallback();
    }

    /**
     * Render
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $logoutUrl = Tk_Type_Url::create('/logout.html');

        if ($this->getAuth()->getUser()) {
            $template->insertText('username', $this->getForm()->getField('username'));
            $user = $this->getAuth()->getUser();

            $homeUrl = $user->getHomeUrl();
            $template->setAttr('userUrl', 'href', $homeUrl->toString());
            $template->setAttr('userBtn', 'href', $homeUrl->toString());


            $template->setAttr('logoutUrl', 'href', $logoutUrl->toString());
            $template->setAttr('logoutBtn', 'href', $logoutUrl->toString());

            $template->insertText('loggedUsername', $user->getUsername());
            $template->setChoice('logout');

            $this->getPage()->getTemplate()->setAttr('_userHomeUrl', 'href', $homeUrl->toString());
            $this->getPage()->getTemplate()->setChoice('_logout');
            $this->getPage()->getTemplate()->insertText('_loginHeading', 'Logout');
        } else {
            $template->setChoice('login');
            $this->getPage()->getTemplate()->setChoice('_login');
            $this->getPage()->getTemplate()->insertText('_loginHeading', 'Login');
        }
        $this->getPage()->getTemplate()->setAttr('_logoutUrl', 'href', $logoutUrl->toString());

        $js = "$(document).ready(function() {
  $('#fid-username').focus();

  $('#fid-password').keypress(function(e) {
      var ascii_code  = e.which;
      var shift_key   = e.shiftKey;
      if( (65 <= ascii_code) && (ascii_code <= 90) && !shift_key) {
        $('.capsOn').show();
      } else {
        $('.capsOn').hide();
      }
  });

});";
        $template->appendJs($js);
    }


    /**
     * Called after a successful login.
     *
     * @param Com_Auth_UserInterface $user
     */
    function loginCallback(Com_Auth_UserInterface $user)
    {
        $url = $user->getHomeUrl();
        $url->redirect();
    }

    /**
     * Called before a logout.
     *
     */
    function logoutCallback()
    {
        $url = new Tk_Type_Url('/index.html');
        $url->redirect();
    }



    /**
     * Get the Auth Object
     *
     * @return Com_Auth
     */
    function getAuth()
    {
        return Com_Auth::getInstance();
    }

}


class Com_Form_Event_Login extends Form_ButtonEvent
{


    /**
     * Create an instance of this object
     *
     * @return Form_Event_Save
     */
    static function create()
    {
        $obj = new self('login');
        return $obj;
    }

    /**
     * (non-PHPdoc)
     * @see lib/Form/Form_Event#execute()
     */
    function execute()
    {
        if ($this->getAuth()->getUser()) {
            $this->getAuth()->setUser(null);
        }
        $username = $this->cleanString($this->getForm()->getFieldValue('username'));
        $password = $this->cleanString($this->getForm()->getFieldValue('password'));

        $user = Com_Auth::findUser($username);

        if ($user) {
            $this->getAuth()->setUser($user, $this->getForm()->getFieldValue('remember'));
            if ($this->getAuth()->isAuthentic($password)) {
                $this->getForm()->getContainer()->loginCallback($user);
            } else {
                $this->getForm()->addFieldError('password', 'Invalid Password.');
            }
        } else {
            $this->getForm()->addFieldError('username', 'Invalid Username.');
        }

        if ($this->getForm()->hasErrors()) {
            $this->getAuth()->setUser(null);
            return;
        }
    }

    /**
     * A function to clean a username and a password.
     *
     * The only valid charcters are: ^a-zA-Z0-9_@.- and a space ' '
     * A space is only valid inside other text and not at the ends, trim gets rid of these.
     *
     * @param string $str
     * @return string
     */
    private function cleanString($str)
    {
        $str = trim($str);
        return preg_replace('/[^a-z0-9_@ \.-]+/i', '', $str);
    }

    /**
     * Get the Auth Object
     *
     * @return Com_Auth
     */
    function getAuth()
    {
        return Com_Auth::getInstance();
    }

}