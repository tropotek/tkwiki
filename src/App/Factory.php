<?php
namespace App;

use App\Dom\Modifier\CategoryList;
use App\Dom\Modifier\SecretList;
use App\Dom\Modifier\Secrets;
use App\Dom\Modifier\WikiImg;
use App\Dom\Modifier\WikiUrl;
use Bs\Ui\Crumbs;
use Dom\Modifier;
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
        $page = new Page($templatePath);
        $page->setDomModifier($this->getTemplateModifier());
        return $page;
    }

    /**
     * @todo Not sure we are using this?
     *       May need to be refactored
     */
    public function createPageFromType(string $pageType = ''): Page
    {
        if (empty($pageType)) $pageType = Page::TEMPLATE_PUBLIC;
        $path = $this->getSystem()->makePath($this->getConfig()->get('path.template.'.$pageType));
        if ($pageType == Page::TEMPLATE_PUBLIC) {
            $template = $this->getRegistry()->get('wiki.default.template', 'default');
            $path = $this->getSystem()->makePath(sprintf('/html/%s.html', $template));
        }
        $page = $this->createPage($path);
        $page->setType($pageType);
        return $page;
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
        $id = 'breadcrumbs.public';
        if (!$this->has($id)) {
            $crumbs = $_SESSION[$id] ?? null;
            // Reset crumbs if wiki home page has been updated
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
                $_SESSION[$id] = $crumbs;
            }
            $this->set($id, $crumbs);
        }
        return $this->get($id);
    }

}