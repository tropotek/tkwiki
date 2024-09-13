<?php
namespace App\Helper;

use App\Db\Page;
use Bs\Traits\SystemTrait;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Db\Filter;


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
        if ($this->getAuthUser()?->isMember()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_MEMBER];
        }
        if ($this->getAuthUser()?->isStaff()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_MEMBER, Page::PERM_STAFF];
        }
        if ($this->getAuthUser()?->isAdmin()) {
            unset($filter['permission']);
        }

        $list = Page::findFiltered(Filter::create($filter, 'title'));

        foreach ($list as $page) {
            if (!$page->canView($this->getAuthUser())) continue;

            if ($this->asTable) {
                $col = $template->getRepeat('col');
                $col->setText('url', $page->title);
                $col->setAttr('url', 'href', $page->getPageUrl());
                $col->setAttr('url', 'title', $page->title);
                $col->appendRepeat();
                $template->setVisible('table');
            } else {
                $li = $template->getRepeat('li');
                $li->setText('url', $page->title);
                $li->setAttr('url', 'href', $page->getPageUrl());
                $li->setAttr('url', 'title', $page->title);
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
  <ul class="" choice="list">
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
