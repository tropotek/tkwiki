<?php
namespace App;

use App\Db\User;
use App\Db\UserMap;
use App\Dom\Modifier\CategoryList;
use App\Dom\Modifier\SecretList;
use App\Dom\Modifier\Secrets;
use App\Dom\Modifier\WikiImg;
use App\Dom\Modifier\WikiUrl;
use Bs\Ui\Crumbs;
use Dom\Mvc\Modifier;
use Symfony\Component\EventDispatcher\EventDispatcher;
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

    public function createPage(string $templatePath = ''): Page
    {
        return Page::create($templatePath);
    }

    public function createUser(): User
    {
        return new User();
    }

    public function getUserMap(): UserMap
    {
        return UserMap::create();
    }

    public function getTemplateModifier(): Modifier
    {
        if (!$this->get('templateModifier')) {
            $dm = parent::getTemplateModifier();
            if ($this->getRegistry()->get('wiki.enable.secret.mod', false)) {
                $dm->addFilter('wikiSecrets', new Secrets());
                $dm->addFilter('wikiSecretList', new SecretList());
            }
            $dm->addFilter('wikiCategoryList', new CategoryList());
            $dm->addFilter('wikiImg', new WikiImg());
            $dm->addFilter('wikiUrl', new WikiUrl());
        }
        return $this->get('templateModifier');
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