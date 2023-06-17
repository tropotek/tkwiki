<?php
namespace App\Helper;

use App\Db\Page;
use App\Db\PageMap;
use Dom\Renderer\DisplayInterface;
use Dom\Template;
use Tk\Traits\SystemTrait;

class PageSelect extends \Dom\Renderer\Renderer implements DisplayInterface
{
    use SystemTrait;

    protected \App\Table\PageSelect $table;

    public function __construct()
    {
        $this->table = new \App\Table\PageSelect();
        $this->table->doDefault($this->getRequest());
        //$this->table->getTable()->resetTableSession();
        $tool = $this->table->getTable()->getTool('title', 25);
        $filter = [
            'published' => true,
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

        $filter = array_merge($this->table->getFilter()->getFieldValues(), $filter);

        $list = PageMap::create()->findFiltered($filter, $tool);
        $this->table->execute($this->getRequest(), $list);

    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        // Add a select wiki page button to the tinyMCE editor.
        $js = <<<JS
jQuery(function($) {
    function insertWikiUrl(title, url, isNew) {
        const editor = tinymce.activeEditor;
        let linkAttrs = {
          href: 'page://' + url,
          title: title
        };
        if (editor.selection.getContent()) {
          editor.execCommand('CreateLink', false, linkAttrs);
        } else {
          editor.insertContent(editor.dom.createHTML('a', linkAttrs, editor.dom.encode(title)));
        }
    }

    $('#page-select-dialog').on('show.bs.modal', function() {
        $('input', this).val('');
    })
    .on('shown.bs.modal', function() {
        $('input', this).last().focus();
    })
    .on('click', '.wiki-insert', function() {
        // On insert existing page event
        let title = $(this).data('page-title');
        let url = $(this).data('page-url');
        insertWikiUrl(title, url, false);
        $('#page-select-dialog').modal('hide');
        return false;
    })
    .on('click', '.btn-create-page', function() {
        // On insert new page event
        let title = $(this).parent().find('input').val();
        let url = title.trim().replace(/[^a-zA-Z0-9_-]/g, '_');
        // TODO: should we check for existing url (ajax) here???
        //       Or we could check on a keyup event (with delay 250ms) and disable btn if exists
        insertWikiUrl(title, url, true);
        $('#page-select-dialog').modal('hide');
        return false;
    });

});
JS;
        $template->appendJs($js);

        // setup the table to be refreshed by javascript on all links/events except cell links
        $js = <<<JS
jQuery(function($) {
    let dialog = $('#page-select-dialog');

    function init() {
        let links = $('th a, .tk-foot a', this).not('[href="javascript:;"], [href="#"]');
        // Handle table links
        links.on('click', function(e) {
            e.stopPropagation();
            let url = $(this).attr('href');
            $('#page-select-table', dialog).load(url + ' #page-select-table', function (response, status, xhr) {
                $('#page-select-table', dialog).trigger(EVENT_INIT);
            });
            return false;
        });
        // Handle table filters
        $('form.tk-form', this).on('submit', function (e) {
            e.stopPropagation();
            let url = $(this).attr('action');
            let data = $(this).serializeArray();
            let submit = $(e.originalEvent.submitter);
            data.push({name: submit.attr('name'), value: submit.attr('value')});
            $('#page-select-table', dialog).load(url + ' #page-select-table', data, function (response, status, xhr) {
                $('#page-select-table', dialog).trigger(EVENT_INIT);
            });
            return false;
        });
    }
    $('#page-select-dialog #page-select-table').on(EVENT_INIT, document, init).each(init);
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