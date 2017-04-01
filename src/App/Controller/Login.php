<?php
namespace App\Controller;

use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Auth;
use Tk\Auth\AuthEvents;
use Tk\Event\AuthEvent;


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
     * @var \Tk\Event\Dispatcher
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
        $this->form->addField(new Event\Link('forgotPassword', \Tk\Uri::create('/recover.html')));
        
        // Find and Fire submit event
        $this->form->execute();

        return $this->show();
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

        try {
            // Fire the login event to allow developing of misc auth plugins
            $event = new AuthEvent($auth, $form->getValues());
            $this->dispatcher->dispatch(AuthEvents::LOGIN, $event);
            // Use the event to process the login like below....
            $result = $event->getResult();
            if (!$result) {
                $form->addError('Invalid username or password');
                return;
            }
            if (!$result->isValid()) {
                $form->addError( implode("<br/>\n", $result->getMessages()) );
                return;
            }
            $this->dispatcher->dispatch(AuthEvents::LOGIN_SUCCESS, $event);
        } catch (\Exception $e) {
            $form->addError($e->getMessage());
        }

    }

    /**
     * show()
     *
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();
        
        if ($this->getConfig()->get('site.user.registration')) {
            $template->setChoice('register');
        }
        
        // Render the form
        $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
        $ren->show();
        
        return $this->getPage()->setPageContent($template);
    }



    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        return \Dom\Loader::loadFile($this->getTemplatePath().'/xtpl/login.xtpl');
    }

}