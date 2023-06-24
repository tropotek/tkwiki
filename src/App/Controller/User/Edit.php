<?php
namespace App\Controller\User;

use App\Db\User;
use App\Db\UserMap;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Uri;

class Edit extends PageController
{
    protected \App\Form\User $form;

    protected string $type = \App\Db\User::TYPE_USER;


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Edit User');
        $this->setAccess(User::PERM_MANAGE_USER | User::PERM_MANAGE_STAFF);
    }

    public function doDefault(Request $request, string $type)
    {
        $this->type = $type;

        /** @var User $user */
        $user = UserMap::create()->find($request->query->getInt('id', 0));
        if ($user && $request->query->get('action') == 'reset') {
            $user->sendRecoverEmail();
            Uri::create()->remove('action')->redirect();
        }

        // Get the form template
        $this->form = new \App\Form\User();
        $this->form->doDefault($request,  $request->query->getInt('id', 0), $type);

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setAttr('back', 'href', $this->getBackUrl());
        $template->setAttr('resend', 'href', Uri::create()->set('action', 'reset'));

        $template->appendText('title', $this->getPage()->getTitle());
        $template->appendTemplate('content', $this->form->show());

        if (!$this->form->getUser()->getId()) {
            $template->setVisible('new-user');
        }

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
      <a href="#" title="Reset password email" data-confirm="Send the reset password email?" class="btn btn-outline-secondary" var="resend"><i class="fa fa-envelope"></i> Reset Password Email</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-users"></i> </div>
    <div class="card-body" var="content">
        <p choice="new-user"><b>NOTE:</b> New users will be sent an email requesting them to activate their account and create a new password.</p>
    </div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }


}