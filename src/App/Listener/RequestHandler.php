<?php
namespace App\Listener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tk\Log;
use Tk\Traits\SystemTrait;

class RequestHandler implements EventSubscriberInterface
{
    use SystemTrait;

    public function onRequest(RequestEvent $event)
    {
        // Check user still logged in, if not use any remember me cookies to auto login and redirect to back to this URI
        if (!$this->getFactory()->getAuthUser()) {
            $user = \App\Db\User::retrieveMe();
            if ($user) {
                Log::alert('user `' . $user->getUsername() . '` auto logged in via cookie');
            }
        }

    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }

}