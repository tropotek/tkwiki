<?php
namespace App\Component;

use App\Db\Content;
use App\Db\Page;
use App\Db\User;
use Bs\Mvc\ComponentInterface;
use Dom\Template;
use Tk\Date;

class PageInfo extends \Dom\Renderer\Renderer implements ComponentInterface
{
    const string CONTAINER_ID = 'page-info-dialog';

    protected Page $page;
    protected Content $content;


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()) return null;

        $pageId = intval($_REQUEST['pageId'] ?? 0);

        $this->page = Page::mustFind($pageId);
        $this->content = $this->page->getContent();

        // Always set the htmx target and swap to end of the surrounding page <body>.
        header('HX-Retarget: body');
        header('HX-Reswap: beforeend');

        return $this->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setAttr('dialog', 'id', self::CONTAINER_ID);

        $template->setText('author', $this->page->getUser()->nameShort);
        $template->setText('title', $this->page->title);
        $template->setText('category', $this->page->category);
        $template->setText('permission', $this->page->getPermissionLabel());
        $template->setText('revision', strval($this->content->contentId));
        $template->setText('views', strval($this->page->views));
        $template->setText('modified', $this->page->modified->format(Date::FORMAT_LONG_DATETIME));
        $template->setText('created', $this->page->modified->format(Date::FORMAT_LONG_DATETIME));

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $dialogId = self::CONTAINER_ID;

        $html = <<<HTML
<div>
    <div class="modal fade" tabindex="-1" var="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Page Information</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body select-table">
                    <ul class="list-unstyled">
                        <li>Title: <span var="title"></span></li>
                        <li>Category: <span var="category"></span></li>
                        <li>Permission: <span var="permission"></span></li>
                        <li>Current Revision: <span var="revision"></span></li>
                        <li>Views: <span var="views"></span></li>
                        <li>Author: <span var="author"></span></li>
                        <li>Modified: <span var="modified"></span></li>
                        <li>Created: <span var="created"></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<script>
jQuery(function($) {
    const dialog = '#{$dialogId}';

    $(dialog).modal('show');

    // remove the dialog element from the dom when it closes
    $(dialog).on('hidden.bs.modal', function() {
        $(dialog).remove();
    });
});
</script>
</div>
HTML;
        return Template::load($html);
    }

}
