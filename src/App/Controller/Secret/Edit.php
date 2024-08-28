<?php
namespace App\Controller\Secret;

use App\Db\Secret;
use App\Db\SecretMap;
use Bs\Form\EditTrait;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use App\Db\User;
use Tk\Alert;
use Tk\Exception;
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
    use EditTrait;

    protected ?Secret $secret = null;


    public function __construct()
    {
        parent::__construct();
        $this->getPage()->setTitle('Edit Secret');
        if (
            !$this->getAuthUser()?->isType(User::TYPE_STAFF) ||
            !$this->getRegistry()->get('wiki.enable.secret.mod', false)
        ) {
            Alert::addWarning('You do not have permission to access the page: <b>' . Uri::create()->getRelativePath() . '</b>');
            Uri::create('/')->redirect();
        }
    }

    public function doDefault(Request $request): \App\Page|\Dom\Mvc\Page
    {
        $this->secret = new Secret();
        $this->secret->setUserId($this->getFactory()->getAuthUser()->getUserId());
        if ($request->query->getInt('secretId')) {
            $this->secret = SecretMap::create()->find($request->query->getInt('secretId'));
        }
        if (!$this->secret) {
            throw new Exception('Invalid ID: ' . $request->query->getInt('secretId'));
        }

        // Get the form template
        $this->setForm(new \App\Form\Secret($this->secret));
        $this->getForm()->init()->execute($request->request->all());

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', $this->getPage()->getTitle());
        $template->setAttr('back', 'href', $this->getBackUrl());

        $template->appendTemplate('content', $this->getForm()->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <div class="page-actions card mb-3">
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