<?php
namespace App\Helper;

use App\Db\Page;
use App\Db\PageMap;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Db\Tool;
use Tk\Traits\SystemTrait;

/**
 * Render the secret output table list
 */
class ViewCategoryList extends Renderer implements DisplayInterface
{
    use SystemTrait;

    protected string $category;

    protected bool $asTable = false;


    public function __construct(string $category, bool $asTable = false)
    {
        $this->category = $category;
        $this->asTable = $asTable;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $filter = [
            'category'   => $this->category,
            'published'  => true,
            'permission' => Page::PERM_PUBLIC
        ];
        if ($this->getFactory()->getAuthUser()->isUser()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_USER];
        }
        if ($this->getFactory()->getAuthUser()->isStaff()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_USER, Page::PERM_STAFF];
        }
        if ($this->getFactory()->getAuthUser()->isAdmin()) {
            unset($filter['permission']);
        }
        $list = PageMap::create()->findFiltered($filter, Tool::create('title'));

        foreach ($list as $page) {
            if (!$page->canView($this->getFactory()->getAuthUser())) continue;

            if ($this->asTable) {
                $col = $template->getRepeat('col');
                $col->setText('url', $page->getTitle());
                $col->setAttr('url', 'href', $page->getPageUrl());
                $col->setAttr('url', 'title', $page->getTitle());
                $col->appendRepeat();
                $template->setVisible('table');
            } else {
                $li = $template->getRepeat('li');
                $li->setText('url', $page->getTitle());
                $li->setAttr('url', 'href', $page->getPageUrl());
                $li->setAttr('url', 'title', $page->getTitle());
                $li->appendRepeat();
                $template->setVisible('list');
            }
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="wk-category-list">
  <ul choice="list">
    <li repeat="li"><a href="#" var="url"></a></li>
  </ul>
  <div class="row g-3" choice="table">
    <div class="col-md-3" repeat="col"><a href="#" var="url"></a></div>
  </div>
</div>
HTML;

        return $this->loadTemplate($html);
    }

}
