<?php
namespace App;

use App\Db\Permissions;
use App\Dom\Modifier\CategoryList;
use App\Dom\Modifier\SecretList;
use App\Dom\Modifier\Secrets;
use App\Dom\Modifier\WikiImg;
use App\Dom\Modifier\WikiUrl;
use Bs\Db\User;
use Bs\Ui\Crumbs;
use Dom\Modifier;
use Tk\Auth\FactoryInterface;

class Factory extends \Bs\Factory implements FactoryInterface
{
    public function createDomPage(string $templatePath = ''): Page
    {
        return new Page($templatePath);
    }

    public function getPermissions(): array
    {
        return Permissions::PERMISSION_LIST;
    }

    public function getAvailablePermissions(?User $user): array
    {
        $list = [];
        if ($user) {
            if ($user->isStaff()) {
                $list = Permissions::PERMISSION_LIST;
            }
        }
        return $list;
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

    public function getCrumbs(): ?Crumbs
    {
        $id = 'breadcrumbs.public';
        if (!$this->has($id)) {
            $crumbs = $_SESSION[$id] ?? null;

            if (!$crumbs instanceof Crumbs) {
                $crumbs = Crumbs::create();
                $crumbs->setTrim(5);
                if (\App\Db\Page::getHomePage()) {
                    $crumbs->setHomeTitle('<i class="fa fa-home"></i>');
                    $crumbs->setHomeUrl('/' . \App\Db\Page::getHomePage()->url);
                }
                $crumbs->reset();
                $_SESSION[$id] = $crumbs;
            }
            $this->set($id, $crumbs);
        }
        return $this->get($id);
    }

}