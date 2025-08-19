<?php
namespace App\Ui;

use App\Db\User;
use Bs\Registry;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Config;

/**
 * Render the secret output
 */
class Navigation extends Renderer implements DisplayInterface
{

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $user = User::getAuthUser();
        if ($user) {
            $template->setVisible('settings', $user->hasPermission(User::PERM_SYSADMIN));
            $template->setVisible('pageManager', $user->isStaff());
            $template->setVisible('menu', $user->hasPermission(User::PERM_SYSADMIN | User::PERM_SYSADMIN));
            $template->setVisible('secret', $user->isStaff() && Registry::getValue('wiki.enable.secret.mod', false));
            $template->setVisible('admin', $user->isAdmin());
            $template->setVisible('dev', $user->isAdmin() && Config::isDev());
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<ul var="nav">
  <li><a class="dropdown-item" href="/profile">My Profile</a></li>
  <li><a class="dropdown-item" href="/settings" var="settings">Site Settings</a></li>
  <li><a class="dropdown-item" href="/pageManager" var="pageManager">Wiki Pages</a></li>
  <li><a class="dropdown-item" href="/menuEdit" var="menu">Menu Edit</a></li>
  <li><a class="dropdown-item" href="/secretManager" var="secret">Secret Manager</a></li>
  <li><hr class="dropdown-divider" choice="admin"></li>
  <li><a class="dropdown-item" href="/sessions" choice="admin">Current Sessions</a></li>
  <li><a class="dropdown-item" href="/tailLog" choice="admin">Tail Log</a></li>
  <li><a class="dropdown-item" href="/info" choice="dev">Info</a></li>
  <li><a class="dropdown-item" href="/_test" choice="dev">Test</a></li>
  <li><a class="dropdown-item" href="/util/dbSize" choice="dev">DB Size</a></li>
  <li><hr class="dropdown-divider"></li>
  <li><a class="dropdown-item" href="#" hx-get="/component/logoutDialog" hx-trigger="click queue:none" hx-target="body" hx-swap="beforeend">Sign out</a></li>
</ul>
HTML;

        return Template::load($html);
    }

}
