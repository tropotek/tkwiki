<?php
namespace App\Controller\Secret;

use App\Db\Secret;
use Bs\ControllerPublic;
use Dom\Template;
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
class Edit extends ControllerPublic
{
    protected ?Secret $secret = null;
    protected ?\App\Form\Secret $form = null;


    public function __construct()
    {

    }

    public function doDefault(): void
    {
        $this->getPage()->setTitle('Edit Secret');
        if (
            !$this->getAuthUser()?->isStaff() ||
            !$this->getRegistry()->get('wiki.enable.secret.mod', false)
        ) {
            Alert::addWarning('You do not have permission to access the page');
            Uri::create('/')->redirect();
        }

        $secretId = intval($_GET['secretId'] ?? 0);

        $this->secret = new Secret();
        $this->secret->userId = $this->getFactory()->getAuthUser()->userId;
        if ($secretId) {
            $this->secret = Secret::find($secretId);
        }
        if (!$this->secret) {
            throw new Exception("cannot find object id {$secretId}");
        }

        $this->form = new \App\Form\Secret($this->secret);
        $this->form->execute($_POST);

    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', $this->getPage()->getTitle());
        $template->setAttr('back', 'href', $this->getBackUrl());

        $template->appendTemplate('content', $this->form->show());

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