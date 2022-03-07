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


    /**
     * get a dom Modifier object
     *
     * @return \Dom\Modifier\Modifier
     */
    public function getDomModifier()
    {
        if (!$this->get('dom.modifier')) {
            $dm = new \Dom\Modifier\Modifier();
            $dm->add(new \Dom\Modifier\Filter\UrlPath($this->getSiteUrl()));
            $dm->add(new \Dom\Modifier\Filter\JsLast());

            // Deprecated no longer using less
            if (class_exists('Dom\Modifier\Filter\Less')) {
                /** @var \Dom\Modifier\Filter\Less $less */
                $vars = array(
                    'siteUrl' => rtrim(\Tk\Uri::create($this->getSiteUrl())->getPath(), '/'),
                    'dataUrl' => rtrim(\Tk\Uri::create($this->getDataUrl())->getPath(), '/'),
                    'templateUrl' => rtrim(\Tk\Uri::create($this->getTemplateUrl())->getPath(), '/') );
                $less = $dm->add(new \Dom\Modifier\Filter\Less($this->getSitePath(), $this->getSiteUrl(), $this->getCachePath(), $vars ));
                $less->setCompress(true);
                $less->setCacheEnabled(!$this->isRefreshCacheRequest());
            }

            if (class_exists('Dom\Modifier\Filter\Scss')) {
                /** @var \Dom\Modifier\Filter\Scss $scss */
                $vars = array(
                    'siteUrl' => rtrim(\Tk\Uri::create($this->getSiteUrl())->getPath(), '/'),
                    'dataUrl' => rtrim(\Tk\Uri::create($this->getDataUrl())->getPath(), '/'),
                    'templateUrl' => rtrim(\Tk\Uri::create($this->getTemplateUrl())->getPath(), '/') );
                $scss = $dm->add(new \Dom\Modifier\Filter\Scss($this->getSitePath(), $this->getSiteUrl(), $this->getCachePath(), $vars));
                $scss->setCompress(true);
                $scss->setCacheEnabled(!$this->isRefreshCacheRequest());
                $scss->setTimeout(\Tk\Date::DAY*14);
            }

            if ($this->isDebug()) {
                $dm->add($this->getDomFilterPageBytes());
            }
            $this->set('dom.modifier', $dm);
        }
        return $this->get('dom.modifier');
    }

}
