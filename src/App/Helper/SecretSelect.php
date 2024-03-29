<?php
namespace App\Helper;

use App\Db\Page;
use App\Db\SecretMap;
use App\Form\Secret;
use App\Ui\FormDialog;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Traits\SystemTrait;

class SecretSelect extends Renderer implements DisplayInterface
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
        if ($this->getFactory()->getAuthUser()->isMember()) {
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
        $form = new Secret(new \App\Db\Secret());
        $form->setHtmx(true);
        $form->init();
        $this->createDialog = new FormDialog($form, 'Create Secret', 'secret-create-dialog');
        $this->createDialog->init();
        $this->createDialog->execute($this->getRequest());

    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        $template->appendTemplate('dialogs', $this->createDialog->show());
        $template->appendTemplate('table', $this->table->show());

        $template->setAttr('user-id', 'data-user-id', $this->getFactory()->getAuthUser()->getUserId());

        // Add a select wiki page button to the tinyMCE editor.
        $js = <<<JS
jQuery(function($) {

    let selectDialog = $('#secret-select-dialog');
    let createDialog = $('#secret-create-dialog');

    function insertSecretHtml(id, name) {
        const editor = tinymce.activeEditor;
        let linkAttrs = {
          class: 'wk-secret',
          'wk-secret': id,
          'title': name,
          src: config.baseUrl + '/html/assets/img/secretbg.png'
        };
        editor.insertContent(editor.dom.createHTML('img', linkAttrs));
    }

    selectDialog.on('show.bs.modal', function() {
        $('input', this).val('');
        $('#secret-select-table', selectDialog).load(document.location.href + ' #secret-select-table', function (response, status, xhr) {
            $('body').trigger(EVENT_INIT_TABLE, $('#secret-select-table', selectDialog));
        });
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
        selectDialog.modal('hide');
        createDialog.modal('show');
        return false;
    });

    $('.btn-insert-list', selectDialog).on('click', function() {
        const editor = tinymce.activeEditor;
        let linkAttrs = {
          class: 'wk-secret-list',
          'wk-secret-list': $(this).data('user-id')
        };
        editor.insertContent(editor.dom.createHTML('div', linkAttrs, editor.dom.encode('{Secret Table Listing}')));
        selectDialog.modal('hide');
    });

    // On create secret
    $('body').on('htmx-stuff', function() {
        console.log('init_form 0');
        // exit if there are errors in the form
        if ($('form .is-invalid', createDialog).length) return;

        let id = $('#secret-secretId', 'form#secret').val();
        let name = $('#secret-name', 'form#secret').val();
        insertSecretHtml(id, name);
        selectDialog.modal('hide');
        createDialog.modal('hide');
    });

    function init() {
        $('.tk-table.secret-table').each(function() {
            let links = $('th a, .tk-foot a', selectDialog).not('[href="javascript:;"], [href="#"]');
            // Handle table links
            links.on('click', function(e) {
                e.stopPropagation();
                let url = $(this).attr('href');
                $('#secret-select-table', selectDialog).load(url + ' #secret-select-table', function (response, status, xhr) {
                    $('body').trigger(EVENT_INIT_TABLE, $('#secret-select-table', selectDialog));
                });
                return false;
            });
            // Handle table filters
            $('form.tk-form', selectDialog).on('submit', function (e) {
                //e.stopPropagation();
                let url = $(this).attr('action');
                let data = $(this).serializeArray();
                let submit = $(e.originalEvent.submitter);
                data.push({name: submit.attr('name'), value: submit.attr('value')});
                $('#secret-select-table', selectDialog).load(url + ' #secret-select-table', data, function (response, status, xhr) {
                    $('body').trigger(EVENT_INIT_TABLE, $('#secret-select-table', selectDialog));
                });
                return false;
            });
        });
    }
    tableEvents.push(init);
    
});
JS;
        $template->appendJs($js);

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
            <button class="btn btn-sm btn-outline-success btn-insert-list" type="button" var="user-id">Insert My List</button>
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