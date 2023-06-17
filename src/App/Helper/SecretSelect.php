<?php
namespace App\Helper;

use App\Db\Page;
use App\Db\SecretMap;
use App\Ui\FormDialog;
use Dom\Renderer\DisplayInterface;
use Dom\Template;
use Tk\Traits\SystemTrait;

class SecretSelect extends \Dom\Renderer\Renderer implements DisplayInterface
{
    use SystemTrait;

    protected \App\Table\SecretSelect $table;

    protected ?FormDialog $createDialog = null;

    public function __construct()
    {
        $this->table = new \App\Table\SecretSelect();
        $this->table->doDefault($this->getRequest());
        //$this->table->getTable()->resetTableSession();
        $tool = $this->table->getTable()->getTool('name', 10);
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

        $list = SecretMap::create()->findFiltered($filter, $tool);
        $this->table->execute($this->getRequest(), $list);


        // Create form dialog
        $form = new \App\Form\Secret(true);
        $this->createDialog = new FormDialog($form, 'Create Secret', 'secret-create-dialog');
        $this->createDialog->init();
        $this->createDialog->execute($this->getRequest());

    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();


        $template->appendTemplate('dialogs', $this->createDialog->show());

        // Add a select wiki page button to the tinyMCE editor.
        $js = <<<JS
jQuery(function($) {
    function insertSecretHtml(id, name) {
        const editor = tinymce.activeEditor;

        // TODO: Aim to make this element an inline-block element then
        //       the author can position it within block elements like a P, DIV element themselves

        let linkAttrs = {
          class: 'wk-secret',
          'wk-module': 'wk-secret',
          'data-secret-id': id,
          'data-name': name
        };

        // TODO:
        // If selected text, select end for insertion
        // if (editor.selection.getContent()) {
        //   editor.execCommand('CreateLink', false, linkAttrs);
        // }
        //editor.getSel().collapseToEnd();

        // TODO: Check if we are inside an already set wk-module element
        //       if so append after not inside this element.
        //       These elements cannot be nested as they will be removed on rendering

        // TODO: Test to see if this appends the element at the end of the selection
        //editor.insertContent(editor.dom.createHTML('span', linkAttrs, editor.dom.encode(name)));
        editor.insertContent(editor.dom.createHTML('span', linkAttrs, editor.dom.encode(name)));

    }

    $('#secret-select-dialog').on('show.bs.modal', function() {
        $('input', this).val('');
    })
    .on('shown.bs.modal', function() {
        $('input', this).last().focus();
    })
    .on('click', '.wiki-insert', function() {
        // insert existing secret
        let id = $(this).data('secret-id');
        let name = $(this).data('secret-name');
        insertSecretHtml(id, name);
        $('#secret-select-dialog').modal('hide');
        return false;
    })
    .on('click', '.btn-create-secret', function() {
        $('#secret-select-dialog').modal('hide');
        $('#secret-create-dialog').modal('show');
        return false;
    });

    // On create secret
    $('body').on(EVENT_INIT_FORM, function() {
        console.log(arguments);
        console.log($('#secret-secret_id', 'form#secret').val());
        let id = $('#secret-secret_id', 'form#secret').val();
        let name = $('#secret-name', 'form#secret').val();
        insertSecretHtml(id, name);
        $('#secret-select-dialog').modal('hide');
        $('#secret-create-dialog').modal('hide');
    });



});
JS;
        $template->appendJs($js);

        // setup the table to be refreshed by javascript on all links/events except cell links
        $js = <<<JS
jQuery(function($) {
    let dialog = $('#secret-select-dialog');

    function init() {
        $('.tk-table.secret-table').each(function() {
            let links = $('th a, .tk-foot a', this).not('[href="javascript:;"], [href="#"]');
            // Handle table links
            links.on('click', function(e) {
                e.stopPropagation();
                let url = $(this).attr('href');
                $('#secret-select-table', dialog).load(url + ' #secret-select-table', function (response, status, xhr) {
                    $('body').trigger(EVENT_INIT_TABLE);
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
                $('#secret-select-table', dialog).load(url + ' #secret-select-table', data, function (response, status, xhr) {
                    $('body').trigger(EVENT_INIT_TABLE);
                });
                return false;
            });
        });
    }

    init();
    $('body').on(EVENT_INIT_TABLE, init);
});
JS;
        $template->appendJs($js);
        $template->appendTemplate('table', $this->table->show());


        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div var="dialogs">
    <div class="modal modal-lg fade" id="secret-select-dialog" tabindex="-1" aria-labelledby="secret-select-label">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="secret-select-label">Select A Secret</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div id="secret-select-table" var="table"></div>
          </div>

          <div class="modal-footer" style="justify-content: space-between;">
            <div>
              <button class="btn btn-sm btn-outline-primary btn-create-secret" type="button">Create</button>
            </div>
            <div class="actions">
              <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>

</div>
HTML;
        return $this->loadTemplate($html);
    }

}