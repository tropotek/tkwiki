<?php
namespace App\Helper;

use App\Db\Permissions;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Traits\SystemTrait;

/**
 * Render the secret output
 */
class Navigation extends Renderer implements DisplayInterface
{
    use SystemTrait;


    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $user = $this->getFactory()->getAuthUser();
        if ($user) {
            $template->setVisible('settings', $user->hasPermission(Permissions::PERM_SYSADMIN));
            $template->setVisible('pageManager', $user->hasPermission(Permissions::PERM_EDITOR));
            $template->setVisible('menu', $user->hasPermission(Permissions::PERM_SYSADMIN | Permissions::PERM_SYSADMIN));
            $template->setVisible('secret', $user && $this->getRegistry()->get('wiki.enable.secret.mod', false));
            $template->setVisible('admin', $user->isAdmin());
            $template->setVisible('dev', $this->getConfig()->isDev());
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
  <li><a class="dropdown-item" href="/listEvents" choice="dev">List Events</a></li>
  <li><hr class="dropdown-divider"></li>
  <li><a class="dropdown-item" href="/logout">Sign out</a></li>
</ul>
HTML;

        return $this->loadTemplate($html);
    }

}
