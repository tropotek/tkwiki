<?php
namespace App;


use App\Db\Permission;

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
        //\Tk\Crumbs::$homeUrl = '/';
    }

    /**
     * @return Db\LockMap
     */
    public function getLockMap()
    {
        $lm = \App\Db\LockMap::getInstance($this->getAuthUser(), $this->getDb());
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
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     * @throws \Exception
     */
    public function setupDispatcher($dispatcher)
    {
        \App\Dispatch::create($dispatcher);
    }

    /**
     * Return the back URI if available, otherwise it will return the home URI
     *
     * @return \Tk\Uri
     */
    public function getBackUrl()
    {
        if ($this->getCrumbs())
            return $this->getCrumbs()->getBackUrl();

        return $this->getSession()->getBackUrl();
    }
    /**
     * @return \Tk\Crumbs
     */
    public function getCrumbs($requestUri = null, $session = null)
    {
        if (!$this->get('crumbs')) {
            $obj = \App\Helper\Crumbs::getInstance($requestUri, $session);
            $this->set('crumbs', $obj);
        }
        return $this->get('crumbs');
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

    /**
     * @return Permission|null
     */
    public function getPermission()
    {
        return Permission::getInstance();
    }


}
