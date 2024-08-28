<?php
namespace App;

use App\Dom\Modifier\CategoryList;
use App\Dom\Modifier\SecretList;
use App\Dom\Modifier\Secrets;
use App\Dom\Modifier\WikiImg;
use App\Dom\Modifier\WikiUrl;
use Bs\Ui\Crumbs;
use Dom\Modifier;
use Tk\Auth\FactoryInterface;
use Tk\Uri;

class Factory extends \Bs\Factory implements FactoryInterface
{
    public function createDomPage(string $templatePath = ''): Page
    {
        return new Page($templatePath);
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
     * @todo: the crumbs have Login as the home link, look into it!
     */
    public function getCrumbs(): ?Crumbs
    {
        $id = 'breadcrumbs.public';
        if (!$this->has($id)) {
            $crumbs = $_SESSION[$id] ?? null;
            // Reset crumbs if wiki home page has been updated
            $rel = Uri::create(\App\Db\Page::getHomeUrl())->getRelativePath();
            $resetUrls = [
                \App\Db\Page::getHomeUrl(),
                '/login',
                '/logout'
            ];

            //if ($crumbs && Uri::create(\App\Db\Page::getHomeUrl())->getRelativePath() == \App\Db\Page::getHomeUrl()) {
            if ($crumbs && in_array($rel, $resetUrls)) {
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
                $_SESSION[$id] = $crumbs;
            }
            $this->set($id, $crumbs);
        }
        return $this->get($id);
    }

}