<?php
namespace App\Helper;

use App\Db\Secret;
use App\Db\SecretMap;
use App\Db\User;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Db\Tool;
use Tk\Traits\SystemTrait;
use Tk\Uri;

/**
 * Render the secret output table list
 */
class ViewSecretList extends Renderer implements DisplayInterface
{
    use SystemTrait;

    protected User $user;


    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $filter = [
            'author' => $this->user->getId()
        ];
        $list = SecretMap::create()->findFiltered($filter, Tool::create('created DESC'));

        foreach ($list as $secret) {
            if (!$secret->canView($this->user)) continue;
            $row = $template->getRepeat('col');
            $ren = new ViewSecret($secret);
            $row->appendTemplate('col', $ren->show());
            $row->appendRepeat();
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="wk-secret-list row g-2">
    <div class="col-md-4" repeat="col"></div>
</div>
HTML;

        return $this->loadTemplate($html);
    }

}
