<?php
namespace App\Helper;

use App\Db\Content;
use App\Db\ContentMap;
use App\Db\Page;
use App\Db\User;
use App\Db\UserMap;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Traits\SystemTrait;
use Tk\Uri;

/**
 * An object to manage and display the wiki Page header
 * information and action buttons.
 */
class PageHeader extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    use SystemTrait;

    const URL_EDIT    = '/edit';
    const URL_HISTORY = '/history';
    const URL_SEARCH  = '/search';

    protected Page $page;

    protected Content $content;

    protected ?User $user = null;


    public function __construct(Page $page, Content $content, ?User $user)
    {
        $this->page = $page;
        $this->content = $content;
        $this->user = $user;
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

        // Page Title
        //$title = str_replace('_', ' ', \Tk\Uri::create()->basename()) ;
        $title = $this->getPage()->getTitle();

        if (!$this->getRegistry()->get('site.page.header.title.hide', false) || $this->getUser()) {
            $template->appendHtml('title', $title);
            $template->setVisible('showTitle');
        }

        // Throw an info style page if no page exists and public user
        if (!$this->getUser()) {
            $template->setVisible('noCreated');
            return $template;
        }

        if ($this->getRegistry()->get('site.page.header.hide') && !$this->getUser()) {
            return $template;
        }

        $list = [];
        if ($this->getPage()->getId())
            $list = ContentMap::create()->findFiltered(['pageId' => $this->getPage()->getId()]);

        if ($this->isEdit()) {
            $template->setVisible('edit');
            if ($this->getPage()->getType() == \App\Db\Page::TYPE_PAGE) {
                $template->setVisible('canView');
            }
            if ($this->getPage()->getId() && count($list)) {
                $template->setVisible('historyBtn');
            }
        } else if ($this->isHistory()) {
            $template->setVisible('history');
            $title .= ' (History)';
        } else {
            if ($this->getRequest()->query->has('contentId')) {
                $template->setVisible('viewRevision');
                $title .= ' <small>(Revision ' . $this->getContent()->getId() . ')</small>';
                $template->addCss('content', 'revision');
                if ($this->getPage()->canEdit($this->getUser())) {
                    $template->setVisible('revert');
                }
            } else {
                $template->setVisible('view');
            }
        }

        if ($this->getUser()) {
            if ($this->getPage()->canEdit($this->getUser())) {
                $url = Uri::create(self::URL_EDIT)->set('pageId', $this->getPage()->getId());
                $template->setAttr('edit', 'href', $url);
                $template->setVisible('canEdit');
            }
            if ($this->getPage()->canDelete($this->getUser())) {
                $url = Uri::create(self::URL_EDIT)->set('pageId', $this->getPage()->getId())->set('del', $this->getPage()->getId());
                $template->setAttr('delete', 'href', $url);
                $template->setVisible('canDelete');
            }

            if ($this->getContent()) {
                $url = Uri::create()->set('pdf', 'pdf');
                $template->setAttr('pdf', 'href', $url);
                $template->setAttr('pdf', 'target', '_blank');
                $template->setVisible('pdfBtn');
            }

            $url = $this->getPage()->getPageUrl();
            if ($this->getPage()->getType() == \App\Db\Page::TYPE_NAV || !$this->getPage()->getId()) {
                $url = Uri::create('/');
            }
            $template->setAttr('cancel', 'href', $url);
        }

        $url = Uri::create(self::URL_HISTORY)->set('pageId', $this->getPage()->getId());
        $template->setAttr('history', 'href', $url);

        if ($this->getContent()) {
            $url = Uri::create(self::URL_HISTORY)->set('r', $this->getContent()->getId());
            $template->setAttr('revert', 'href', $url);

            $template->setText('contentId', $this->getContent()->getId());
        }

        $url = Uri::create($this->getPage()->getUrl());
        $template->setAttr('view', 'href', $url);

        $template->setText('permission', ucfirst($this->getPage()->getPermissionLabel()));
        $template->addCss('permission', $this->getPage()->getPermissionLabel());


        // TODO: Implement show() method.
        // modified
        $content = $this->getPage()->getContent();
        if ($content) {
            //$template->insertText('modified', $content->modified->format(\Tk\Date::LONG_DATETIME));
            $template->setText('modified', \Tk\Date::toRelativeString($content->getModified()));
            $template->setVisible('modified');
        }

        // contributors
        $contentList = ContentMap::create()->findContributors($this->getPage()->getId());
        $html = [];
        /** @var \stdClass $c */
        foreach($contentList as $i => $c) {
            /** @var User $user */
            $user = UserMap::create()->find($c->user_id);
            if (!$user) continue;
            $url = Uri::create(self::URL_SEARCH)->set('search-terms', 'user:'.$user->getHash());
            $class = array();
            $title = \Tk\Date::toRelativeString(\Tk\Date::create($c->created));
            if ($this->getPage()->getUser()->getId() == $user->getId()) {
                $class[] = 'author';
                $title = 'Contributed: ' . $title;
            }
            $html[] = sprintf('<a href="%s" class="%s" title="%s">%s</a>', $url, implode(' ', $class), $title, $user->getName());
        }
        if (count($html)) {
            $template->insertHtml('contrib', implode(', ', $html));
            $template->setVisible('contrib');
        }

        $template->setVisible('showHeadInfo');

        return $template;
    }

    /**
     * Is the page an edit page
     */
    public function isEdit(): bool
    {
        if (Uri::create()->basename() == 'edit.html') {
            return true;
        }
        return false;
    }

    /**
     * Is the page the view history page
     */
    public function isHistory(): bool
    {
        if (Uri::create()->basename() == 'history.html') {
            return true;
        }
        return false;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div var="content">
  <div class="clearfix wiki-header">
    <div class="head-title" choice="showTitle">
      <h1 var="title"></h1>
    </div>
    <div class="row" choice="showHeadInfo">
    <div class="col-xs-6">
      <p class="wiki-meta contrib" choice="contrib"><strong>Contributers:</strong> <span var="contrib"></span></p>
      <p class="wiki-meta modified" choice="modified"><strong>Modified:</strong> <span var="modified"></span></p>
    </div>
    <div class="col-xs-6 text-right edit" choice="edit">
      <p class="wiki-meta">
        <a href="#" title="Delete The Page" class="btn btn-danger btn-xs wiki-delete-trigger" var="delete" choice="canDelete"><i class="fa fa-remove"></i> Delete</a>
      </p>
      <p class="wiki-meta">
        <a href="#" title="Save The Page" class="btn btn-primary btn-xs wiki-save-trigger" var="save" choice="canEdit"><i class="fa fa-save"></i> Save</a>
        <a href="#" title="View The Page" class="btn btn-default btn-xs" var="view" choice="canView"><i class="fa fa-eye"></i> View</a>
        <a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="history" choice="historyBtn"><i class="fa fa-clock-o"></i> History</a>
        <!--  a href="#" title="Delete The Page" class="btn btn-danger btn-xs wiki-delete-trigger" var="delete" choice="canDelete"><i class="fa fa-remove"></i> Delete</a -->
        <a href="#" title="Cancel Edit Page" class="btn btn-default btn-xs" var="cancel"><i class="fa fa-ban"></i> Cancel</a>
      </p>
    </div>
    <div class="col-xs-6 text-right" choice="view">
      <p class="wiki-meta view">
        <a href="#" title="Edit The Page" class="btn btn-default btn-xs" var="edit" choice="canEdit"><i class="fa fa-pencil"></i> Edit</a>
        <a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="history" choice="canEdit"><i class="fa fa-clock-o"></i> History</a>
        <a href="#" title="Download PDF" class="btn btn-default btn-xs" var="pdf" choice="pdfBtn"><i class="fa fa-file-pdf-o"></i> Download</a>
      </p>
      <p class="wiki-meta permission"><strong>Page Permission:</strong> <span var="permission">Public</span> - <strong>Revision:</strong> <span var="contentId">0</span></p>
    </div>
    <div class="col-xs-6 text-right" choice="history">
      <p class="wiki-meta view">
        <a href="#" title="Edit The Page" class="btn btn-default btn-xs" var="edit" choice="canEdit"><i class="fa fa-pencil"></i> Edit</a>
        <a href="#" title="View The Page" class="btn btn-default btn-xs" var="view"><i class="fa fa-eye"></i> View</a>
      </p>
      <p class="wiki-meta permission"><strong>Page Permission:</strong> <span var="permission">Public</span> - <strong>Revision:</strong> <span var="contentId">0</span></p>
    </div>
    <div class="col-xs-6 text-right" choice="viewRevision">
      <p class="wiki-meta view">
        <a href="#" title="Page Revision History" class="btn btn-danger btn-xs wiki-revert-trigger" var="revert" choice="revert"><i class="fa fa-share"></i> Revert</a>
        <a href="#" title="Page Revision History" class="btn btn-default btn-xs" var="history" choice="canEdit"><i class="fa fa-clock-o"></i> History</a>
        <a href="#" title="Download PDF" class="btn btn-default btn-xs" var="pdf" choice="pdfBtn"><i class="fa fa-file-pdf-o"></i> Download</a>
      </p>
      <p class="wiki-meta permission"><strong>Page Permission:</strong> <span var="permission">Public</span> - <strong>Revision:</strong> <span var="contentId">0</span></p>
    </div>
    </div>
  </div>
</div>
HTML;

        return $this->loadTemplate($html);
    }

}
