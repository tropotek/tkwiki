<?php
namespace App\Controller\Admin;

use App\Db\User;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;

class Settings extends PageController
{
    protected \App\Form\Settings $form;


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Edit Settings');
        $this->setAccess(User::PERM_SYSADMIN);
        $this->getRegistry()->save();
        $this->getCrumbs()->reset();
    }

    public function doDefault(Request $request)
    {
        // Get the form template
        $this->form = new \App\Form\Settings();

        $this->form->doDefault($request);

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());

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
    <div class="card-header" var="title"><i class="fa fa-cogs"></i> </div>
    <div class="card-body" var="content"></div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }


}