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
     * init the default params.
     */
    protected function init()
    {
        parent::init();
        \Tk\Crumbs::$homeUrl = '/';
    }

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
        return \Tk\Uri::create('/');
    }

    /**
     * @return \Bs\Listener\PageTemplateHandler
     */
    public function getPageTemplateHandler()
    {
        if (!$this->get('page.template.handler')) {
            $this->set('page.template.handler', new \App\Listener\PageTemplateHandler());
        }
        return $this->get('page.template.handler');
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
     * @throws \Exception
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


    /**
     * @return Db\RoleMap
     */
    public function getRoleMapper()
    {
        if (!$this->get('obj.mapper.role')) {
            $this->set('obj.mapper.role', Db\RoleMap::create());
        }
        return $this->get('obj.mapper.role');
    }

    /**
     * @return Db\Role
     */
    public function createRole()
    {
        return new Db\Role();
    }


    /**
     * @param $form
     * @return \Tk\Form\Renderer\Dom
     */
    public function createFormRenderer($form)
    {
        $obj = \Tk\Form\Renderer\Dom::create($form);
        $obj->setFieldGroupRenderer($this->getFormFieldGroupRenderer($form));
        //$obj->getLayout()->setDefaultCol('col-sm-12');
        return $obj;
    }

    /**
     * @param \Tk\Form $form
     * @return \Tk\Form\Renderer\FieldGroup
     */
    public function getFormFieldGroupRenderer($form)
    {
        return \Tk\Form\Renderer\FieldGroup::create($form);
    }

    /**
     * @param string $templatePath (optional)
     * @return Page
     */
    public function createPage($templatePath = '')
    {
        try {
            return new Page($templatePath);
        } catch (\Exception $e) {
            \Tk\Log::error($e->__toString());
        }
    }


}