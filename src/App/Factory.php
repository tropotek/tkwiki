<?php
namespace App;

use App\Db\PageMap;
use App\Db\User;
use App\Db\UserMap;
use App\Dom\Modifier\AppAttributes;
use App\Dom\Modifier\WikiUrl;
use Bs\Db\UserInterface;
use Bs\Ui\Crumbs;
use Dom\Mvc\Modifier;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tk\Auth\Adapter\AdapterInterface;
use Tk\Auth\Adapter\AuthUser;
use Tk\Auth\FactoryInterface;
use Tk\Uri;

class Factory extends \Bs\Factory implements FactoryInterface
{

    public function initEventDispatcher(): ?EventDispatcher
    {
        if ($this->getEventDispatcher()) {
            new Dispatch($this->getEventDispatcher());
        }
        return $this->getEventDispatcher();
    }

    public function createPage($templatePath, callable $onCreate = null): Page
    {
        $page = Page::create($templatePath);
        if ($onCreate) {
            call_user_func_array($onCreate, [$page]);
        }
        return $page;
    }

    public function getTemplateModifier(): Modifier
    {
        if (!$this->get('templateModifier')) {
            $dm = parent::getTemplateModifier();
            $dm->addFilter('appAttributes', new AppAttributes());
            $dm->addFilter('appWikiUrl', new WikiUrl());
        }
        return $this->get('templateModifier');
    }

    /**
     * Return a User object or record that is located from the Auth's getIdentity() method
     * Override this method in your own site's Factory object
     * @return null|UserInterface|User Null if no user logged in
     */
    public function getAuthUser(): ?UserInterface
    {
        if (!$this->has('authUser')) {
            if ($this->getAuthController()->hasIdentity()) {
                $user = UserMap::create()->findByUsername($this->getAuthController()->getIdentity());
                $this->set('authUser', $user);
            }
        }
        return $this->get('authUser');
    }

    /**
     * This is the default Authentication adapter
     * Override this method in your own site's Factory object
     */
    public function getAuthAdapter(): AdapterInterface
    {
        if (!$this->has('authAdapter')) {
            $adapter = new AuthUser(UserMap::create());
            $this->set('authAdapter', $adapter);
        }
        return $this->get('authAdapter');
    }

    /**
     * get the breadcrumb storage object
     */
    public function getCrumbs(): ?Crumbs
    {
        //$this->getSession()->set('breadcrumbs', null);
        if (!$this->has('breadcrumbs')) {
            $crumbs = $this->getSession()->get('breadcrumbs');
            if ($crumbs && Uri::create(\App\Db\Page::getHomeUrl())->getRelativePath() != $crumbs->getHomeUrl()) {
                $crumbs = null;
            }
            if (!$crumbs instanceof Crumbs) {
                $crumbs = Crumbs::create();
                $crumbs->setTrim(5);
                if (\App\Db\Page::getHomeUrl()) {
                    $crumbs->setHomeTitle('<i class="fa fa-home"></i>');
                    $crumbs->setHomeUrl(\App\Db\Page::getHomeUrl());
                }
                $crumbs->reset();
                $this->getSession()->set('breadcrumbs', $crumbs);
            }
            $this->set('breadcrumbs', $crumbs);
        }
        return $this->get('breadcrumbs');
    }
}