<?php
namespace App\Helper;

use App\Db\Page;
use App\Db\PageMap;
use App\Db\User;
use Dom\Template;
use Tk\Traits\SystemTrait;

/**
 * An object to manage and display the wiki Page header
 * information and action buttons.
 */
class Menu extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    use SystemTrait;

    protected ?User $user = null;

    protected array $list = [];


    public function __construct(User $user)
    {
        $this->user = $user;
        $this->init();
    }

    public function init(): void
    {
        $list = PageMap::create()->findNavPages();
        foreach($list as $page) {
            if ($page->getPermission() == Page::PERM_PUBLIC) {
                $this->list[] = $page;
                continue;
            }
            if ($this->user && $page->canView($this->user)) {
                $this->list[] = $page;
            }
        }
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        if ($this->user && Page::canCreate($this->getFactory()->getAuthUser())) {
            $template->setVisible('canCreate');
            $url = \Tk\Uri::create('/edit')->set('type', Page::TYPE_NAV);
            $template->setAttr('create', 'href', $url);
        }

        /** @var Page $page */
        foreach($this->list as $page) {
            if (!$page->canView($this->user)) return $template;
            $row = $template->getRepeat('row');
            $row->setText('title', $page->getTitle());

            $content = $page->getContent();
            $event = new \App\Event\ContentEvent($content);
            $this->getFactory()->getEventDispatcher()->dispatch($event, \App\WikiEvents::WIKI_CONTENT_VIEW);

            $row->insertHtml('html', $content->getHtml());

            if ($this->user && $page->canEdit($this->user)) {
                $url = \Tk\Uri::create('/edit')->set('id', $page->getId());
                $row->setAttr('edit', 'href', $url);
                $row->setVisible('edit');
            }
            $row->appendRepeat();
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<ul class="nav navbar-nav">
  <li class="dropdown mega-dropdown" repeat="row">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span var="title"></span> <span class="caret"></span></a>
    <ul class="dropdown-menu mega-dropdown-menu">
      <li class="col-sm-12">
        <div class="wiki-menu-edit pull-right" choice="edit">
          <a href="#" class="btn btn-primary btn-sm wiki-menu-edit-btn" var="edit"><i class="fa fa-pencil"></i> Edit</a>
        </div>
        <div class="wiki-menu-content" var="html"></div>
      </li>
    </ul>
  </li>
  <li class="wiki-menu-create" choice="canCreate"><a href="#" class="navbar-toggle" title="New Menu Tab" var="create"><span class="fa fa-plus"></span></a></li>
</ul>
HTML;
        return $this->loadTemplate($html);
    }

}
