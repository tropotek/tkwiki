<?php
namespace App;

use App\Console\Cron;
use App\Console\SecretUp;
use App\Console\Test;
use App\Console\TestData;
use App\Console\WikiTest;
use App\Console\Zap;
use App\Dom\Modifier\CategoryList;
use App\Dom\Modifier\SecretList;
use App\Dom\Modifier\Secrets;
use App\Dom\Modifier\WikiImg;
use App\Dom\Modifier\WikiUrl;
use Bs\Ui\Crumbs;
use Dom\Modifier;
use Symfony\Component\Console\Application;
use Tk\System;

class Factory extends \Bs\Factory
{
    public function createDomPage(string $templatePath = ''): Page
    {
        if (str_starts_with(basename($templatePath), 'default') && is_file(System::makePath($this->getRegistry()->get('wiki.default.template', '')))) {
            $templatePath = System::makePath($this->getRegistry()->get('wiki.default.template', $templatePath));
        }
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

    public function getConsole(): Application
    {
        if (!$this->has('console')) {
            $app = parent::getConsole();
            // Setup App Console Commands
            $app->add(new Cron());
            //$app->add(new SecretUp());
            //$app->add(new Zap());
            if ($this->getConfig()->isDev()) {
                $app->add(new WikiTest());
                $app->add(new TestData());
                $app->add(new Test());
            }
        }
        return $this->get('console');
    }
}