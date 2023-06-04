<?php
namespace App\Helper;

use App\Db\Content;
use App\Db\Page;
use App\Db\User;
use Bs\Ui\Dialog;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Traits\SystemTrait;
use Tk\Uri;

/**
 * The view page toolbar button group and its actions
 */
class ViewToolbar extends Renderer implements DisplayInterface
{
    use SystemTrait;

    protected Page $page;

    protected Content $content;

    protected ?User $user = null;

    //protected ?Dialog $dialog = null;


    public function __construct(Page $page)
    {
        $this->page = $page;
        $this->content = $page->getContent();
        $this->user = $this->getFactory()->getAuthUser();
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        if ($this->getPage()->canEdit($this->getUser())) {
            $template->setAttr('edit-url', 'href', Uri::create('/edit')->set('id', $this->getPage()->getId()));
            $template->setVisible('edit-url');
        }
        $template->setAttr('pdf-url', 'href', Uri::create()->set('pdf'));

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="btn-group btn-group-sm float-end" role="group" aria-label="Small button group">
  <a href="/edit?pageId=1" title="Edit The Page" class="btn btn-outline-secondary" choice="edit-url"><i class="fa fa-fw fa-pencil"></i></a>
  <a href="/?pdf=pdf" title="Download PDF" class="btn btn-outline-secondary" target="_blank" var="pdf-url"><i class="fa fa-fw fa-file-pdf"></i></a>
  <a href="javascript:window.print();" title="Print Document" class="btn btn-outline-secondary"><i class="fa fa-fw fa-print"></i></a>
  <a href="javascript:alert('TODO: Implement a dialog with page info....');" title="Page Info" class="btn btn-outline-secondary"><i class="fa fa-fw fa-circle-info"></i></a>
</div>
HTML;

        return $this->loadTemplate($html);
    }

}
