<?php
namespace App;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Config extends \Bs\Config
{

    /**
     * @return Db\LockMap
     */
    public function getLockMap()
    {
        $lm = \App\Db\LockMap::getInstance($this->getUser(), $this->getDb());
        return $lm;
    }

    /**
     * Return the users home|dashboard relative url
     *
     * @param \Bs\Db\User|null $user
     * @return \Tk\Uri
     */
    public function getUserHomeUrl($user = null)
    {
//        if (!$user) $user = $this->getUser();
//        if ($user) {
//            if ($user->isAdmin())
//                return \Tk\Uri::create('/admin/index.html');
//            if ($user->isUser())
//                return \Tk\Uri::create('/user/index.html');
//        }
        return \Tk\Uri::create('/');
    }

    /**
     * @return \Bs\Listener\AuthHandler
     */
    public function getAuthHandler()
    {
        if (!$this->get('auth.handler')) {
            $this->set('auth.handler', new \App\Listener\AuthHandler());
        }
        return $this->get('auth.handler');
    }

    /**
     * @param null $user
     * @return \App\Auth\Acl
     */
    public function getAcl($user = null)
    {
        if (!$user) $user = $this->getUser();
        $obj = \App\Auth\Acl::create($user);
        return $obj;
    }

    /**
     * @param \Tk\Event\Dispatcher $dispatcher
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function setupDispatcher($dispatcher)
    {
        \App\Dispatch::create($dispatcher);
    }

    /**
     * @return \Bs\Listener\PageTemplateHandler
     */
    public function getCrumbsHandler()
    {
        if (!$this->get('handler.crumbs')) {
            $this->set('handler.crumbs', null);
        }
        return $this->get('handler.crumbs');
    }



}