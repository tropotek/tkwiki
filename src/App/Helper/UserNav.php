<?php
namespace App\Helper;

use App\Db\Secret;
use App\Db\SecretMap;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Traits\SystemTrait;
use Tk\Uri;

/**
 * Render the secret output
 */
class UserNav extends Renderer implements DisplayInterface
{
    use SystemTrait;


    public function __construct()
    {
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();


        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<ul var="nav">
  <li><a class="dropdown-item" href="/profile">My Profile</a></li>
  <li><a class="dropdown-item" href="/settings" app-has-perm="PERM_SYSADMIN">Site Settings</a></li>
  <li><a class="dropdown-item" href="/staffManager" app-has-perm="PERM_MANAGE_STAFF">Staff</a></li>
  <li><a class="dropdown-item" href="/userManager" app-has-perm="PERM_MANAGE_USER | PERM_MANAGE_STAFF">Users</a></li>
  <li><a class="dropdown-item" href="/pageManager" app-has-perm="PERM_EDITOR">Wiki Pages</a></li>
  <li><a class="dropdown-item" href="/orphanManager" app-has-perm="PERM_EDITOR">Orphaned Pages</a></li>
  <li><a class="dropdown-item" href="/menuEdit" app-has-perm="PERM_SYSADMIN | PERM_EDITOR">Menu Edit</a></li>
  <li><a class="dropdown-item" href="/secretManager" app-is-type="TYPE_STAFF">Secret Manager</a></li>
  <li><hr class="dropdown-divider" app-has-perm="PERM_ADMIN"></li>
  <li><a class="dropdown-item" href="/tailLog" app-has-perm="PERM_ADMIN">Tail Log</a></li>
  <li><a class="dropdown-item" href="/listEvents" app-has-perm="PERM_ADMIN">List Events</a></li>
  <li><hr class="dropdown-divider" app-is-type="TYPE_STAFF"></li>
  <li><a class="dropdown-item" href="/logout">Sign out</a></li>
</ul>
HTML;

        return $this->loadTemplate($html);
    }

}
