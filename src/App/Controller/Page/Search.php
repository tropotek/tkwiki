<?php
namespace App\Controller\Page;

use App\Db\Page;
use Bs\ControllerPublic;
use Dom\Template;
use Tk\Db;
use Tk\Db\Filter;


class Search extends ControllerPublic
{
    const SID = 'search.terms';

    protected array $rows    = [];
    protected string $search = '';
    protected int    $total  = 0;

    public function doDefault(): void
    {
        $this->getPage()->setTitle('Search Results');
        $this->getCrumbs()->reset();

        $this->search = trim($_POST['s'] ?? $_SESSION[self::SID] ?? '');
        if (isset($_POST['s'])) {
            $_SESSION[self::SID] = $this->search;
            \Tk\Uri::create()->remove('s')->redirect();
        }

        $filter = Filter::create([
            'fullSearch' => $this->search,
            'published' => true,
            'userId' => $this->getFactory()->getAuthUser()->userId,
            'permission' => Page::PERM_PUBLIC,
        ], '-modified', 250);
        if ($this->getFactory()->getAuthUser()->isMember()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_MEMBER];
        }
        if ($this->getFactory()->getAuthUser()->isStaff()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_MEMBER, Page::PERM_STAFF];
        }

        if ($this->search) {
            $this->rows = Page::findViewable($filter);
            $this->total = Db::getLastStatement()->getTotalRows();
        }

    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        foreach($this->rows as $page) {
            if (!$page->canView($this->getAuthUser())) continue;

            $rpt = $template->getRepeat('row');
            $rpt->setText('title', $page->title);
            $rpt->setAttr('title', 'title', $page->title);
            $rpt->setAttr('title', 'href', $page->getUrl());

            $rpt->setAttr('link', 'href', $page->getUrl());
            $rpt->setText('link', $page->getUrl());

            $rpt->setText('description', 'No Content.');
            $rpt->setText('date', $page->getCreated()->format(\Tk\Date::FORMAT_MED_DATE));
            $rpt->setText('time', $page->getCreated()->format('H:i'));

            if ($page->getContent()) {
                $description = $page->getContent()->description;
                // This is a security risk as is can show sensitive data from the content, do not do this...
                if (!$description) {
                    $description = trim(substr(strip_tags(html_entity_decode($page->getContent()->html)), 0, 256));
                }

                $rpt->setHtml('description', htmlentities($description));
                $rpt->setText('author', $page->getUser()->getName());
                $rpt->setText('date', $page->getContent()->getCreated()->format(\Tk\Date::FORMAT_MED_DATE));
                $rpt->setText('time', $page->getContent()->getCreated()->format('H:i'));
                if (trim($page->getContent()->keywords)) {
                    $rpt->setText('keywords', $page->getContent()->keywords);
                    $rpt->setVisible('keywords');
                }
            }

            $rpt->appendRepeat();
        }

        $terms = '"'.$this->search.'"';
        $template->setText('terms', $terms);
        $template->setText('found', $this->total);

        $css = <<<CSS
.wiki-search .search-result h4 {
    margin-bottom: 0;
    color: #1E0FBE;
}
.wiki-search .search-result h4 a {
    text-decoration: none;
}
.wiki-search .search-result .search-link {
    color: #006621;
    text-decoration: none;
}
.wiki-search .search-result p {
    font-size: 14px;
    margin-top: 5px;
}
.wiki-search ul {

}
.wiki-search ul li {
  font-size: 12px;
  color: #666;
}
.wiki-search .hr-line-dashed {
    border-top: 1px dashed #E7EAEC;
    color: #ffffff;
    background-color: #ffffff;
    height: 1px;
    margin: 20px 0;
}

CSS;
        $template->appendCss($css);

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <h2 var="title">Search Results</h2>
  <div class="wiki-search" var="content">
    <div class="search-head">
      <h5 class="">
        <strong class="text-danger" var="found">0</strong>
        results were found for the search for
        <strong class="text-danger" var="terms"></strong>
      </h5>
    </div>
    <div class="hr-line-dashed"></div>
    <div repeat="row">
      <div class="search-result">
        <h4><a href="#" var="title"></a></h4>
        <a href="#" class="search-link" var="link"></a>
        <p var="description"></p>
        <ul class="list-inline">
          <li class="list-inline-item" title="Author"><i class="fa fa-fw fa-user"></i> <span var="author"></span></li>
          <li class="list-inline-item" title="Modified Date"><i class="fa fa-fw fa-calendar"></i> <span var="date"></span></li>
          <li class="list-inline-item" title="Modified Time"><i class="fa fa-fw fa-clock"></i> <span var="time"></span></li>
          <li class="list-inline-item" title="Tags" choice="keywords"><i class="fa fa-fw fa-tags"></i> <span var="keywords"></span></li>
        </ul>
      </div>
      <div class="hr-line-dashed"></div>
    </div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}