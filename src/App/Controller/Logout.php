<?php
namespace App\Controller;

use Tk\Request;
use Tk\Auth\AuthEvents;
use Tk\Event\AuthEvent;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Logout extends Iface
{

    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        $event = new AuthEvent($this->getConfig()->getAuth());
        $this->getConfig()->getEventDispatcher()->dispatch(AuthEvents::LOGOUT, $event);
        
        \Tk\Uri::create('/')->redirect();
    }

}