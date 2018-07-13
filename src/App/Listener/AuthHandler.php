<?php
namespace App\Listener;

use Tk\Event\GetResponseEvent;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AuthHandler extends \Bs\Listener\AuthHandler
{



    /**
     * do any auth init setup
     *
     * @param GetResponseEvent $event
     * @throws \Tk\Db\Exception
     * @throws \Exception
     */
    public function onRequest(GetResponseEvent $event)
    {
        // if a user is in the session add them to the global config
        // Only the identity details should be in the auth session not the full user object, to save space and be secure.
        $config = \App\Config::getInstance();
        $auth = $config->getAuth();
        $user = null;                       // public user

        if ($auth->getIdentity()) {         // Check if user is logged in
            $user = $config->getUserMapper()->findByUsername($auth->getIdentity());
            $config->setUser($user);
        }

        // Get page access permission from route params (see config/routes.php)
        $role = $event->getRequest()->getAttribute('role');

        // no role means page is publicly accessible
        if (!$role || empty($role)) return;
        if ($user) {
            if (!$config->getAcl($user)->hasRole($role)) {
                // Could redirect to a authentication error page.
                \Tk\Alert::addWarning('You do not have access to the requested page.');
                $config->getUserHomeUrl($user)->redirect();
            }
        } else {
            \Tk\Uri::create('/login.html')->redirect();
        }
    }
    
}