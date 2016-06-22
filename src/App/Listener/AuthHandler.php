<?php
namespace App\Listener;


use Tk\EventDispatcher\SubscriberInterface;
use App\Event\AuthEvent;
use Tk\Kernel\KernelEvents;
use Tk\Event\ControllerEvent;


/**
 * Class StartupHandler
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AuthHandler implements SubscriberInterface
{

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogin(AuthEvent $event)
    {
        $config = \App\Factory::getConfig();
        $result = null;
        $adapterList = $config->get('system.auth.adapters');
        foreach($adapterList as $name => $class) {
            $adapter = \App\Factory::getAuthAdapter($class, $event->all());
            $result = $event->getAuth()->authenticate($adapter);
            if ($result && $result->getCode() == \Tk\Auth\Result::SUCCESS) { 
                $config->setUser(\App\Db\User::getMapper()->findByUsername($event->getAuth()->getIdentity()));
                $event->setResult($result);
                $config->getUser()->lastLogin = \Tk\Date::create();
                $config->getUser()->save();
                return;
            }
        }
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogout(AuthEvent $event)
    {
        $event->getAuth()->clearIdentity();
    }
    
    /**
     * Check the user has access to this controller
     *
     * @param ControllerEvent $event
     */
    public function onControllerAccess(ControllerEvent $event)
    {
        /** @var \App\Controller\Iface $controller */
        $controller = current($event->getController());
        $user = $controller->getUser();
        if ($controller instanceof \App\Controller\Iface) {
            
            $access = $controller->getAccess();
            
            // Check the user has access to the controller in question
            if (empty($access)) return;
            if (!$user) \Tk\Uri::create('/login.html')->redirect();
            if (!$user->getAccess()->hasRole($access)) { 
            //if (!\App\Auth\Access::create($user)->hasRole($access)) {
                // Could redirect to a authentication error page...
                // Could cause a loop if the permissions are stuffed
                \App\Alert::getInstance()->addWarning('You do not have access to the requested page.');
                \Tk\Uri::create($user->getHomeUrl())->redirect();
            }
        }
    }

    /**
     * 
     * 
     * @param \App\Event\FormEvent $event
     */
    public function onRegister(\App\Event\FormEvent $event)
    {
        /** @var \App\Db\User $user */
        $user = $event->get('user');

        // Add some default roles
        // TODO: Get these from the config/settings...
        foreach ([3,4,5,6,7] as $roleId) {
            \App\Db\Role::getMapper()->addUserRole($roleId, $user->id);
        }
        
        
        
        // on success email user confirmation
        $message = \Dom\Loader::loadFile($event->get('templatePath').'/xtpl/mail/account.registration.xtpl');
        $message->insertText('name', $user->name);
        $url = \Tk\Uri::create()->set('h', $user->hash);
        $message->insertText('url', $url->toString());
        $message->setAttr('url', 'href', $url->toString());
        
        // TODO: Send the email here
        vd($message->toString());
        
    }

    public function onRegisterConfirm(\Tk\Event\RequestEvent $event)
    {
        /** @var \App\Db\User $user */
        $user = $event->get('user');

        // Send an email to confirm account active 
        $message = \Dom\Loader::loadFile($event->get('templatePath').'/xtpl/mail/account.activated.xtpl');
        $message->insertText('name', $user->name);
        $url = \Tk\Uri::create('/login.html');
        $message->insertText('url', $url->toString());
        $message->setAttr('url', 'href', $url->toString());
        
        // TODO: Send the email here
        vd($message->toString());
        
    }

    public function onRecover(\Tk\Event\RequestEvent $event)
    {
        /** @var \App\Db\User $user */
        $user = $event->get('user');
        $pass = $event->get('password');
        
        // Send an email to confirm account active 
        $message = \Dom\Loader::loadFile($event->get('templatePath').'/xtpl/mail/account.recover.xtpl');
        $message->insertText('name', $user->name);
        $message->insertText('password', $pass);
        $url = \Tk\Uri::create('/login.html');
        $message->insertText('url', $url->toString());
        $message->setAttr('url', 'href', $url->toString());
        
        // TODO: Send the email here
        vd($message->toString());
        
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onControllerAccess',
            'auth.onLogin' => 'onLogin',
            'auth.onLogout' => 'onLogout',
            'auth.onRegister' => 'onRegister',
            'auth.onRegisterConfirm' => 'onRegisterConfirm',
            'auth.onRecover' => 'onRecover'
        );
    }
    
    
}