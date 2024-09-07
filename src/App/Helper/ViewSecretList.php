<?php
namespace App\Helper;

use App\Db\Secret;
use Bs\Db\User;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Traits\SystemTrait;
use Tt\DbFilter;

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
        if (!$this->getRegistry()->get('wiki.enable.secret.mod', false)) {
            return $template;
        }

        $filter = [
            'userId' => $this->user->userId
        ];
        $list = Secret::findFiltered(DbFilter::create($filter, 'name'));

        foreach ($list as $secret) {
            if (!$secret->canView($this->getFactory()->getAuthUser())) continue;
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
