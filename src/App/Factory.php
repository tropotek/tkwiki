<?php
namespace App;

use App\Console\Cron;
use App\Console\Test;
use App\Console\TestData;
use App\Console\WikiTest;
use App\Db\User;
use App\Dom\Modifier\CategoryList;
use App\Dom\Modifier\SecretList;
use App\Dom\Modifier\Secrets;
use App\Dom\Modifier\WikiImg;
use App\Dom\Modifier\WikiUrl;
use Bs\Auth;
use Bs\Db\UserInterface;
use Bs\Registry;
use Bs\Ui\Breadcrumbs;
use Dom\Modifier;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tk\Config;
use Tk\Path;

class Factory extends \Bs\Factory
{

    public function initEventDispatcher(): ?EventDispatcher
    {
        if (!$this->has('eventDispatcher')) {
            new Listeners($this->getEventDispatcher());
        }
        return $this->getEventDispatcher();
    }

    public function createDomPage(string $templatePath = ''): Page
    {
        // settings default template
        if (str_starts_with(basename($templatePath), 'default') && is_file(Path::create(Registry::getValue('wiki.default.template', '/html/default.html')))) {
            $templatePath = Path::create(Registry::getValue('wiki.default.template', $templatePath));
        }
        return new Page($templatePath);
    }

    public function getTemplateModifier(): Modifier
    {
        if (!$this->get('templateModifier')) {
            $dm = parent::getTemplateModifier();
            if (Registry::getValue('wiki.enable.secret.mod', false)) {
                $dm->addModifier(new Secrets());
                $dm->addModifier(new SecretList());
            }
            $dm->addModifier(new CategoryList());
            $dm->addModifier(new WikiImg());
            $dm->addModifier(new WikiUrl());
        }
        return $this->get('templateModifier');
    }

    public function initBreadcrumbs(): Breadcrumbs
    {
        $crumbs = Breadcrumbs::init();
        Breadcrumbs::setHome('/' . \App\Db\Page::getHomePage()->url, '<i class="fa fa-home"></i>');
        return $crumbs;
    }

    public function getConsole(): Application
    {
        if (!$this->has('console')) {
            $app = parent::getConsole();
            // Setup App Console Commands
            $app->add(new Cron());
            if (Config::isDev()) {
                //$app->add(new WikiTest());
                //$app->add(new TestData());
                $app->add(new Test());
            }
        }
        return $this->get('console');
    }

    /**
     *
     */
    public function createNewUser(string $username, string $email, string $password, int $perms = 0, string $type = ''): ?UserInterface
    {
        $user = new User();
        $user->givenName = ucfirst($username);
        $user->type = $type ?: User::TYPE_STAFF;
        $user->country = 'AU';
        $user->save();

        $auth = Auth::create($user);
        $auth->username = $username;
        $auth->email = $email;
        $auth->permissions = $perms;
        $auth->password = Auth::hashPassword($password);
        $auth->save();

        return $user;
    }
}