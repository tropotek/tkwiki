<?php
namespace App;

use App\Controller\Menu\View;
use App\Db\User;
use App\Helper\Navigation;
use Bs\Auth;
use Bs\Ui\Dialog;
use Dom\Modifier\JsLast;
use Dom\Template;
use Tk\Alert;
use Tk\Uri;

class Page extends \Bs\Mvc\Page
{

    public function show(): ?Template
    {
        $template = parent::show();

        $secretEnabled = json_encode(boolval($this->getRegistry()->get('wiki.enable.secret.mod', false)));
        $js = <<<JS
tkConfig.enableSecretMod = {$secretEnabled};
JS;
        $template->appendJs($js, array(JsLast::$ATTR_PRIORITY => -9990));

        $template->appendMetaTag('keywords', $this->getRegistry()->get('system.meta.keywords', ''));
        $template->appendMetaTag('description', $this->getRegistry()->get('system.meta.description', ''));


        $template->appendJs($this->getRegistry()->get('system.global.js', ''));
        $template->appendCss($this->getRegistry()->get('system.global.css', ''));

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
        $this->showCreatePageDialog();

        // all pages
        $this->showAlert();
        $this->showCrumbs();
        $this->showLogoutDialog();

        if (Auth::getAuthUser()) {
            $template->setText('username', Auth::getAuthUser()->username);
        }

        $userNav = new Navigation();
        $template->replaceTemplate('user-nav', $userNav->show());

        return $template;
    }

    /**
     * Show a logout confirmation dialog
     */
    protected function showLogoutDialog(): void
    {
        //if (!(Auth::getAuthUser() && isset($_SESSION['_OAUTH']))) return;
        if (!(Auth::getAuthUser())) return;
        $oAuth = $_SESSION['_OAUTH'] ?? '';

        $html = <<<HTML
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="get" action="/logout">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="logoutModalLabel">Logout</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to leave?

          <div class="form-check" choice="ssi">
            <input class="form-check-input" type="checkbox" name="ssi" value="1" id="fid-ssi-logout">
            <label class="form-check-label" for="fid-ssi-logout" var="label">
              Logout from Microsoft
            </label>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Logout</button>
        </div>
      </form>
    </div>
  </div>
</div>

HTML;
        $template = $this->loadTemplate($html);

        if ($oAuth && $this->getConfig()->get('auth.'.$oAuth.'.endpointLogout', '')) {
            $template->setText('label', 'Logout from ' . ucwords($oAuth));
            $template->setVisible('ssi');
        }

        $js = <<<JS
jQuery(function($) {
    $('.btn-logout').on('click', function() {
        $('#logoutModal').modal('show');
        return false;
    });
});
JS;
        $template->appendJs($js);

        $this->getTemplate()->prependTemplate('content', $template);
    }

    protected function showCreatePageDialog(): void
    {
        $dialog = new Dialog('Create a page', 'create-page-dialog');

        $dialog->addButton('Cancel')->addCss('btn btn-outline-secondary');
        $dialog->addButton('Create')->addCss('btn btn-outline-primary btn-create');

        $html = <<<HTML
<div>
   <div class="mb-3">
     <label for="create-page-title" class="form-label">Select a title for your new page:</label>
     <input type="text" name="title" id="create-page-title" class="form-control" placeholder="Page Title">
   </div>
</div>
HTML;
        $dialog->setContent($html);
        $js = <<<JS
jQuery(function ($) {
    $('.btn-create', '#create-page-dialog').on('click', function () {
        let url = $('#create-page-title').val().trim().replace(/[^a-zA-Z0-9_-]/g, '_');
        if (url) {
            document.location = tkConfig.baseUrl + '/edit?u=' + url;
        }
        $('#create-page-dialog').modal('hide');
    });
});
JS;
        $this->getTemplate()->appendJs($js);

        $this->getTemplate()->appendBodyTemplate($dialog->show());
    }

    protected function showMenu(): void
    {
        $menu = new View($this->getTemplate());
        $menu->show();
    }

    protected function showCrumbs(): void
    {
        $crumbs = $this->getFactory()->getCrumbs();
        $crumbs->addCss('mt-2 ');

        if (!$crumbs->isVisible()) return;

        $template = $crumbs->show();
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