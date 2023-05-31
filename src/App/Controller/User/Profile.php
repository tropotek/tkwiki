<?php
namespace App\Controller\User;

use App\Db\User;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * TODO: Create a user profile edit page.
 */
class Profile extends PageController
{
    protected \App\Form\User $form;


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Edit User');
        $this->setAccess(User::PERM_MANAGE_USER | User::PERM_MANAGE_STAFF);
    }

    public function doDefault(Request $request, $id)
    {
        // Get the form template
        $this->form = new \App\Form\User();
        $this->form->doDefault($request, $id);

        return $this->getPage();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setText('title', $this->getPage()->getTitle());

        //$template->appendTemplate('content', $this->form->getRenderer()->getTemplate());
        $template->appendTemplate('content', $this->form->show());

        return $template;
    }

    public function __makeTemplate()
    {
        $html = <<<HTML
<div>
  <h2 var="title"></h2>
  <div var="content" class="tk-form-content"></div>
</div>
HTML;
        return $this->loadTemplate($html);
    }


}