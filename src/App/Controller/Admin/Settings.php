<?php
namespace App\Controller\Admin;

use App\Db\User;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;

class Settings extends PageController
{
    protected \App\Form\Settings $form;


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Edit Settings');
        $this->setAccess(User::PERM_SYSADMIN);
        $this->getRegistry()->save();
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
        $template->setText('title', $this->getPage()->getTitle());

        $template->appendTemplate('content', $this->form->show());

        return $template;
    }

    public function __makeTemplate()
    {
        $html = <<<HTML
<div>
  <h2 var="title"></h2>
  <div var="content"></div>
</div>
HTML;
        return $this->loadTemplate($html);
    }


}