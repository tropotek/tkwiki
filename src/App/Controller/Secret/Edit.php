<?php
namespace App\Controller\Secret;

use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use App\Db\User;
use Tk\Alert;
use Tk\Uri;

/**
 * Add Route to /src/config/routes.php:
 * ```php
 *   $routes->add('secret-manager', '/secretEdit')
 *       ->controller([App\Controller\Secret\Edit::class, 'doDefault']);
 * ```
 */
class Edit extends PageController
{

    protected ?\App\Db\Secret $secret = null;

    protected \App\Form\Secret $form;


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Edit Secret');
        if (
            !$this->getAuthUser()?->isType(User::TYPE_STAFF) ||
            !$this->getRegistry()->get('wiki.enable.secret.mod', false)
        ) {
            Alert::addWarning('You do not have permission to access the page: <b>' . Uri::create()->getRelativePath() . '</b>');
            Uri::create('/')->redirect();
        }
    }

    public function doDefault(Request $request)
    {
        if ($request->get('secretId')) {
            $this->secret = \App\Db\SecretMap::create()->find($request->get('id', 0));
        }

        // Get the form template
        $this->form = new \App\Form\Secret();
        $this->form->doDefault($request, $request->query->get('id', 0));

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', $this->getPage()->getTitle());
        $template->setAttr('back', 'href', $this->getFactory()->getBackUrl());

        $template->appendTemplate('content', $this->form->show());

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