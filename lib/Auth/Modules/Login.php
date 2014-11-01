<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *
 *
 * @package Auth
 */
class Auth_Modules_Login extends Com_Web_Component
{
    /**
     * @var boolean
     */
    protected $remember = true;

    protected $recoverUrl = null;

    protected $registerUrl = null;




    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->addEvent('a', 'doActivate');
        $this->addEvent('u', 'doUnregister');

        $this->addEvent('logout', 'doLogout');
        $this->addEvent('lo', 'doLogout');
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
  <h2>Login</h2>

  <div class="wrapper">
      <p class="notice" choice="activate">
        Your account is now active, you can login at anytime.<br/>
        Thank You!
      </p>
      <p class="notice" choice="unregister">
        Your account has been disabled and can no longer be accessed.
        If this was not your intention please <a href="/contactUs.html" rel="nofollow">contact us</a> and
        let us know that you want your account re-activated.
      </p>

      <p class="notice logout" choice="logout">
        You are currently logged in as `<strong var="loggedUsername"></strong>`, you can return <a href="#" var="userBtn" title="Return to home page" rel="nofollow">to your account page</a>,
        <a href="#" var="logoutBtn" title="Logout" rel="nofollow">logout</a> or you can use the form to login as another user.
      </p>
      <p class="note" choice="login">
        Enter your username and password to access this site. If you have unintentionally landed on this page you can return to the <a href="/index.html" title="Home Page">home page</a>.
      </p>
      <p class="capsOn" style="display: none;"><em>Warning:</em> Your `Caps Lock` key is on!</p>
      <div var="_Login"></div>
  </div>
</div>';
        return Com_Web_Template::load($html);
    }

    /**
     * init
     *
     */
    function init()
    {

        $form = Form::create('_Login');
        $form->addEvent(Auth_Form_Event_Login::create());

        $el = $form->addField(Form_Field_Text::create('username'))->setRequired(true);
        if (Tk_Request::exists('username')) {
            $el->setValue(Tk_Request::get('username'));
        }
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

    function doDefault()
    {
        if (Auth::getUser()) {
            if (Tk_Request::exists('username')) {
                if (Auth::getUser()->getUsername() != Tk_Request::get('username')) {
                    Auth::clear();
                    return;
                }
            }
            Auth::getUserHome()->redirect();
        }
    }

    /**
     * doLogout
     *
     */
    function doActivate()
    {
        $user = Auth::getEvent()->findByHash(Tk_Request::get('a'));
        if (!$user) {
            return;
        }
        Auth::activateUser($user);
        Tk_Session::set('__activate', true);

        Tk_Request::requestUri()->delete('a')->redirect();
    }

    /**
     * doLogout
     *
     * @todo: need to create an email confirmation to disable the account.
     */
    function doUnregister()
    {
        $user = Auth::getEvent()->findByHash(Tk_Request::get('u'));
        if (!$user) {
            return;
        }
        $user->setActive(false);
        Tk_Session::set('__unregister', true);

        Tk_Request::requestUri()->delete('u')->redirect();
    }

    /**
     * doLogout
     *
     */
    function doLogout()
    {
        Auth::logout();
    }

    /**
     * Render
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        if (Tk_Session::getOnce('__activate')) {
            $template->setChoice('activate');
        }
        if (Tk_Session::getOnce('__unregister')) {
            $template->setChoice('unregister');
        }

        $logoutUrl = Tk_Type_Url::create('/logout.html');

        if (Auth::getUser()) {
            $template->insertText('username', $this->getForm()->getField('username'));
            $user = Auth::getUser();

            $homeUrl = Auth::getUserHome();
            $template->setAttr('userUrl', 'href', $homeUrl);
            $template->setAttr('userBtn', 'href', $homeUrl);

            $template->setAttr('logoutUrl', 'href', $logoutUrl);
            $template->setAttr('logoutBtn', 'href', $logoutUrl);

            $template->insertText('loggedUsername', $user->getUsername());
            $template->setChoice('logout');

            $this->getPage()->getTemplate()->setAttr('_userHomeUrl', 'href', $homeUrl);
            $this->getPage()->getTemplate()->setChoice('_logout');
            $this->getPage()->getTemplate()->insertText('_loginHeading', 'Logout');
        } else {
            $template->setChoice('login');
            $this->getPage()->getTemplate()->setChoice('_login');
            $this->getPage()->getTemplate()->insertText('_loginHeading', 'Login');
        }
        $this->getPage()->getTemplate()->setAttr('_logoutUrl', 'href', $logoutUrl);

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


}


class Auth_Form_Event_Login extends Form_ButtonEvent
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
        Auth::clear();
        $username = $this->getForm()->getFieldValue('username');
        $password = $this->getForm()->getFieldValue('password');
        $user = Auth::getEvent()->findByUsername($username);

        if (!$user) {
            $this->getForm()->addFieldError('username', 'Invalid Username.');
            return;
        }

        if (!Auth::isAuthentic($user, $password)) {
            $this->getForm()->addFieldError('password', 'Invalid Password.');
            return;
        }

        Auth::login($user, $this->getForm()->getFieldValue('remember'));
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
        //return preg_replace('/[^a-z0-9_ \.-]+/i', '', $str);
        return $str;
    }

}