<?php
namespace App\Controller\Secret;

use Symfony\Component\HttpFoundation\Request;
use Bs\PageController;
use Dom\Template;
use App\Db\User;
use Tk\Uri;

/**
 * Add Route to /src/config/routes.php:
 * ```php
 *   $routes->add('secret-manager', '/secretManager')
 *       ->controller([App\Controller\Secret\Manager::class, 'doDefault']);
 * ```
 */
class Manager extends PageController
{
    protected \App\Table\Secret $table;

    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Secret Manager');
        $this->setAccess(User::PERM_MANAGE_STAFF);
    }

    public function doDefault(Request $request)
    {

        // Get the form template
        $this->table = new \App\Table\Secret();
        $this->table->doDefault($request);
        $this->table->execute($request);

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', $this->getPage()->getTitle());
        $template->setAttr('create', 'href', Uri::create('/secretEdit'));

        $template->appendTemplate('content', $this->table->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <div class="card mb-3">
    <div class="card-header"><i class="fa fa-cogs"></i> Actions</div>
    <div class="card-body" var="actions">
      <a href="/" title="Back" class="btn btn-outline-secondary" var="back"><i class="fa fa-arrow-left"></i> Back</a>
      <a href="/" title="Create Secret" class="btn btn-outline-secondary" var="create"><i class="fa fa-plus"></i> Create Secret</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-users"></i> </div>
    <div class="card-body" var="content"></div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}