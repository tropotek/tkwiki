<?php
namespace App\Component;

use App\Db\Page;
use App\Db\User;
use Bs\Mvc\Table;
use Dom\Template;
use Tk\Db;
use Tk\Form\Field\Input;
use Tk\Table\Cell;

class PageSelect extends \Dom\Renderer\Renderer
{
    const string CONTAINER_ID = 'page-select';

    protected Table $table;
    protected bool $showCreate = true;


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()) return null;

        $this->showCreate = truefalse($_GET['sc'] ?? true);

        $this->table = new Table('page-select-tbl');
        $this->table->hideReset();
        $this->table->setOrderBy('title');
        $this->table->setLimit(10);
        $this->table->addCss('tk-table-sm');

        $this->table->appendCell('title')
            ->addHeaderCss('max-width')
            ->setSortable(true)
            ->addOnValue(function(Page $page, Cell $cell) {
                return sprintf('<a href="javascript:;" class="wiki-insert"
                    data-page-id="%s" data-page-title="%s" data-page-url="%s" title="Insert a page link">%s</a>',
                    $page->pageId, $page->title, $page->url, $page->title);
            });

        $this->table->appendCell('category')
            ->setSortable(true)
            ->addOnValue(function(Page $page, Cell $cell) {
                return sprintf('<a href="javascript:;" class="wiki-cat-list"
                    data-category="%s" title="Insert a category table">%s</a>',
                    $page->category, $page->category);
            });

        $this->table->appendCell('userId')
            ->addOnValue(function(Page $page, Cell $cell) {
                return $page->getUser()->nameShort ?? '';
            });

        $this->table->appendCell('permission')
            ->addOnValue(function(Page $page, Cell $cell) {
                return \App\Db\Page::PERM_LIST[$page->permission] ?? '';
            });


        // Add Filter Fields
        $this->table->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search title or category');

        // execute table
        $this->table->execute();

        // Set the table rows
        $filter = $this->table->getDbFilter();
        $filter->replace([
            'publish' => true,
            'userId' => User::getAuthUser()->userId,
            'permission' => Page::PERM_PUBLIC,
        ]);
        if (User::getAuthUser()->isMember()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_MEMBER];
        }
        if (User::getAuthUser()->isStaff()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_MEMBER, Page::PERM_STAFF];
        }
        $list = Page::findViewable($filter);
        $this->table->setRows($list, Db::getLastStatement()->getTotalRows());

        return $this->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setAttr('dialog', 'id', self::CONTAINER_ID);

        // convert all table urls to HTMX urls
        $this->table->getRenderer()->setMaxPages(5);
        $template->appendTemplate('content', $this->table->htmxShow());

        if ($this->showCreate) {
            $template->setVisible('show-create');
        }

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $dialogId = self::CONTAINER_ID;

        $html = <<<HTML
<div>
  <div class="modal fade" tabindex="-1" var="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Select Wiki Page</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body select-table" var="content"></div>
        <div class="modal-footer" style="justify-content: space-between;" choice="show-create">
          <div>
              <div class="input-group input-group-sm">
                <input type="text" class="form-control" placeholder="New Page Title" >
                <button class="btn btn-outline-primary btn-create-page" type="button">Create</button>
              </div>
          </div>
          <div class="actions">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
  jQuery(function($) {
    const dialog = '#{$dialogId}';

    $(dialog).on('show.bs.modal', function(e) {
        $('input', this).val('');
    })
    .on('shown.bs.modal', function() {
        if (tinymce.activeEditor) {
            let title = tinymce.activeEditor.selection.getContent({ format: 'text' });
            if (title !== '') {
                $('input', this).last().val(title);
            }
            $('input', this).last().focus();
        }
    })
    .on('click', '.wiki-insert', function() {
        // On insert existing page event
        let title = $(this).data('pageTitle');
        let url = $(this).data('pageUrl');
        let pageId = $(this).data('pageId');
        $(dialog).trigger('selected.ps.modal', [title, url, pageId]);
        $(dialog).modal('hide');
        return false;
    })
    .on('click', '.btn-create-page', function() {
        // On insert new page event
        let title = $(this).parent().find('input').val();
        let url = title.trim().replace(/[^a-zA-Z0-9_-]/g, '_');
        $(dialog).trigger('selected.ps.modal', [title, url, 0]);
        $(dialog).modal('hide');
        return false;
    })
    .on('click', '.wiki-cat-list', function() {
        // On insert new page event
        let category = $(this).data('category');
        let attrs = {
          'wk-category-list': category
        };
        $(dialog).trigger('catSelect.ps.modal', [category, attrs]);
        $(dialog).modal('hide');
        return false;
    });

  });
</script>

</div>
HTML;
        return Template::load($html);
    }

}
