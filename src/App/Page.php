<?php
namespace App;

use App\Controller\Menu\View;
use App\Helper\UserNav;
use Bs\Ui\Dialog;
use Dom\Template;
use Tk\Uri;

class Page extends \Bs\Page
{

    public function show(): ?Template
    {
        $template = parent::show();

        if ($this->getRegistry()->get('system.meta.keywords')) {
            $template->appendMetaTag('keywords', $this->getRegistry()->get('system.meta.keywords', ''));
        }
        if ($this->getRegistry()->get('system.meta.description')) {
            $template->appendMetaTag('description', $this->getRegistry()->get('system.meta.description', ''));
        }

        $template->appendJs($this->getRegistry()->get('system.global.js', ''));
        $template->appendCss($this->getRegistry()->get('system.global.css', ''));

        // all pages
        $this->showAlert();

        // public page
        if ($this->getTemplatePath() == $this->getFactory()->getPublicPage()->getTemplatePath()) {
            $this->showMenu();
            $this->showCrumbs();
            $this->showCreatePageDialog();
        }

        if ($this->getFactory()->getAuthUser()) {
            $template->setText('username', $this->getFactory()->getAuthUser()->getUsername());
        }

        $userNav = new UserNav();
        $template->replaceTemplate('user-nav', $userNav->show());

        return $template;
    }


    protected function showCreatePageDialog(): void
    {
        $dialog = new Dialog('Create a page', 'create-page-dialog');

        $dialog->addButton('Cancel')->addCss('btn btn-outline-secondary');
        $dialog->addButton('Create')->addCss('btn btn-outline-primary btn-create');

        $html = <<<HTML
<div>
   <div class="mb-3">
     <label for="create-page-title" class="form-label">Select a title for your new page:</label>
     <input type="text" name="title" id="create-page-title" class="form-control" placeholder="Page Title">
   </div>
</div>
HTML;
        $dialog->setContent($html);
        $js = <<<JS
jQuery(function ($) {
    $('.btn-create', '#create-page-dialog').on('click', function () {
        let url = $('#create-page-title').val().trim().replace(/[^a-zA-Z0-9_-]/g, '_');
        if (url) {
            document.location = config.baseUrl + '/edit?u=' + url;
        }
        $('#create-page-dialog').modal('hide');
    });
});
JS;
        $this->getTemplate()->appendJs($js);

        $this->getTemplate()->appendBodyTemplate($dialog->show());
    }

    protected function showMenu(): void
    {
        $menu = new View($this->getTemplate());
        $menu->show();
    }

    protected function showCrumbs(): void
    {
        $crumbs = $this->getFactory()->getCrumbs();
        if (!($crumbs && $crumbs->isVisible())) return;
        $template = $crumbs->show();
        if ($this->getTemplate()->hasVar('crumbs')) {
            $this->getTemplate()->insertTemplate('crumbs', $template);
        } else {
            $this->getTemplate()->prependTemplate('container', $template);
        }
    }


    protected function showAlert(): void
    {
        if (!count($this->getFactory()->getSession()->getFlashBag()->peekAll())) return;

        $html = <<<HTML
<div var="alertPanel">
  <div class="alert alert-dismissible fade show" role="alert" repeat="alert">
    <i choice="icon"></i>
    <strong var="title"></strong>
    <span var="message"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
</div>
HTML;
        $template = $this->loadTemplate($html);

        $template->setAttr('alertPanel', 'hx-get', Uri::create('/api/htmx/alert'));
        foreach ($this->getFactory()->getSession()->getFlashBag()->all() as $type => $flash) {
            foreach ($flash as $a) {
                $a = unserialize($a);
                $r = $template->getRepeat('alert');
                $css = strtolower($type);
                if ($css == 'error') $css = 'danger';
                $r->addCss('alert', 'alert-' . $css);
                //$r->setText('title', ucfirst(strtolower($type)));
                $r->insertHtml('message', $a->message);
                if ($a->icon) {
                    $r->addCss('icon', $a->icon);
                    $r->setVisible('icon');
                }
                $r->appendRepeat();
            }
        }
        // TODO: see how prepending to content goes, may need its
        //       own div tag for easier placement of the alerts
        if ($this->getTemplate()->hasVar('alert')) {
            $this->getTemplate()->insertTemplate('alert', $template);
        } else {
            $this->getTemplate()->prependTemplate('content', $template);
        }
    }

}