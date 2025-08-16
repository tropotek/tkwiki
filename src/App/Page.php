<?php
namespace App;

use App\Controller\Menu\View;
use App\Db\User;
use App\Ui\Navigation;
use Bs\Auth;
use Bs\Registry;
use Bs\Ui\Breadcrumbs;
use Dom\Modifier\JsLast;
use Dom\Template;
use Tk\Alert;
use Tk\Config;
use Tk\Uri;

class Page extends \Bs\Mvc\Page
{

    public function show(): ?Template
    {
        $template = parent::show();

        $secretEnabled = json_encode(boolval(Registry::getValue('wiki.enable.secret.mod', false)));
        $js = <<<JS
tkConfig.enableSecretMod = {$secretEnabled};
JS;
        $template->appendJs($js, array(JsLast::$ATTR_PRIORITY => -9990));

        $template->appendMetaTag('keywords', Registry::getValue('system.meta.keywords', ''));
        $template->appendMetaTag('description', Registry::getValue('system.meta.description', ''));


        $template->appendJs(Registry::getValue('system.global.js', ''));
        $template->appendCss(Registry::getValue('system.global.css', ''));

        $template->setText('year', date('Y'));
        $template->setAttr('home', 'href', Uri::create('/')->toString());

        $user = User::getAuthUser();
        if (is_null($user)) {
            $template->setVisible('no-auth');
            $template->setVisible('loggedOut');
        } else {
            $template->setText('username', $user->username);
            $template->setText('user-name', $user->nameShort);
            $template->setText('user-type', ucfirst($user->type));
            $template->setAttr('user-image', 'src', $user->getImageUrl());
            $template->setAttr('user-home-url', 'href', $user->getHomeUrl());

            $template->setVisible('loggedIn');
            $template->setVisible('auth');
        }

        // public page
        $this->showMenu();
        //$this->showCreatePageDialog();

        // all pages
        $this->showAlert();
        $this->showCrumbs();

        if (Auth::getAuthUser()) {
            $template->setText('username', Auth::getAuthUser()->username);
        }

        $userNav = new Navigation();
        $template->replaceTemplate('user-nav', $userNav->show());

        return $template;
    }

    protected function showMenu(): void
    {
        $menu = new View($this->getTemplate());
        $menu->show();
    }

    protected function showCrumbs(): void
    {
        if (!Breadcrumbs::instance()->isVisible()) return;

        $html = <<<HTML
<div>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb" var="crumbs">
      <li class="breadcrumb-item" repeat="item"><a href="#" var="url"></a></li>
    </ol>
  </nav>
</div>
HTML;
        $template = Template::load($html);

        $i = 0;
        $last = Breadcrumbs::count() - 1;
        foreach (Breadcrumbs::toArray() as $url => $title) {
            $repeat = $template->getRepeat('item');

            $repeat->setAttr('url', 'href', $url);
            $repeat->setHtml('url', $title);

            // last item
            if ($i >= $last) {
                //$repeat->setHtml('item', $title); // disable link on last crumb
                $repeat->addCss('item', 'active');
                $repeat->setAttr('item', 'aria-current', 'page');
            }

            $repeat->appendRepeat();
            $i++;
        }

        if ($this->getTemplate()->hasVar('crumbs')) {
            $this->getTemplate()->insertTemplate('crumbs', $template);
        } else {
            $this->getTemplate()->prependTemplate('container', $template);
        }
    }

    protected function showAlert(): void
    {
        if (!Alert::hasAlerts()) return;

        $html = <<<HTML
<div var="alertPanel">
  <div class="alert alert-dismissible fade show" role="alert" repeat="alert">
    <i choice="icon"></i>
    <strong var="title"></strong>
    <span var="message"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
</div>
HTML;
        $template = $this->loadTemplate($html);

        $template->setAttr('alertPanel', 'hx-get', Uri::create('/api/htmx/alert'));
        foreach (Alert::getAlerts() as $type => $flash) {
            foreach ($flash as $a) {
                $r = $template->getRepeat('alert');
                $css = strtolower($type);
                if ($css == 'error') $css = 'danger';
                $r->addCss('alert', 'alert-' . $css);
                //$r->setText('title', ucfirst(strtolower($type)));
                $r->setHtml('message', $a->message);
                if ($a->icon) {
                    $r->addCss('icon', $a->icon);
                    $r->setVisible('icon');
                }
                $r->appendRepeat();
            }
        }

        if ($this->getTemplate()->hasVar('alert')) {
            $this->getTemplate()->insertTemplate('alert', $template);
        } else {
            $this->getTemplate()->prependTemplate('content', $template);
        }
    }

}