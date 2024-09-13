<?php
namespace App\Helper;

use App\Db\Page;
use Bs\Traits\SystemTrait;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Db;

class PageSelect extends Renderer implements DisplayInterface
{
    use SystemTrait;

    protected \App\Table\PageSelect $table;

    public function __construct()
    {
        $this->table = new \App\Table\PageSelect();
        $this->table->setOrderBy('title');
        $this->table->execute();

        $filter = $this->table->getDbFilter();
        $filter->replace([
            'published' => true,
            'userId' => $this->getAuthUser()->userId,
            'permission' => Page::PERM_PUBLIC,
        ]);
        if ($this->getAuthUser()->isMember()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_MEMBER];
        }
        if ($this->getAuthUser()->isStaff()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_MEMBER, Page::PERM_STAFF];
        }

        $list = Page::findViewable($filter);
        $this->table->setRows($list, Db::getLastStatement()->getTotalRows());

    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        // Add a select wiki page button to the tinyMCE editor.
        $js = <<<JS
jQuery(function($) {
    let dialog = '#page-select-dialog';

    $(dialog).on('show.bs.modal', function() {
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

    // setup page select dialog table
    tkRegisterInit(function () {
        let links = $('th a, .tk-foot a', dialog).not('[href="javascript:;"], [href="#"]');

        // Handle table links
        links.on('click', function(e) {
            e.stopPropagation();
            let url = $(this).attr('href');
            $('#page-select-table', dialog).load(url + ' #page-select-table', function (response, status, xhr) {
                tkInit($(dialog));
            });
            return false;
        });
        $('.tk-foot select').prop('disabled', true);

        // Handle table filters
        $('form.tk-form', dialog).on('submit', function (e) {
            e.stopPropagation();
            let url = $(this).attr('action');
            let data = $(this).serializeArray();
            let submit = $(e.originalEvent.submitter);
            data.push({name: submit.attr('name'), value: submit.attr('value')});
            $('#page-select-table', dialog).load(url + ' #page-select-table', data, function (response, status, xhr) {
                tkInit($(dialog));
            });
            return false;
        });
    });

});
JS;
        $template->appendJs($js);
        $template->appendTemplate('table', $this->table->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div class="modal modal-lg fade" id="page-select-dialog" tabindex="-1" aria-labelledby="page-select-label">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="page-select-label">Select A Page</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="page-select-table" var="table"></div>
      </div>

      <div class="modal-footer" style="justify-content: space-between;">
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
HTML;
        return $this->loadTemplate($html);
    }

}