<?php
namespace App\Component;

use App\Db\Page;
use App\Db\Secret;
use App\Db\User;
use Bs\Mvc\ComponentInterface;
use Bs\Mvc\Table;
use Dom\Template;
use Tk\Db;
use Tk\Form\Field\Input;
use Tk\Table\Cell;
use Tk\Uri;

class SecretSelect extends \Dom\Renderer\Renderer implements ComponentInterface
{
    const string CONTAINER_ID = 'secret-select';

    protected Table $table;
    protected bool  $showCreate = true;


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()) return null;

        $this->showCreate = truefalse($_GET['sc'] ?? true);

        $this->table = new Table('secret-select-tbl');
        $this->table->hideReset();
        $this->table->setOrderBy('name');
        $this->table->setLimit(10);
        $this->table->addCss('tk-table-sm');

        $this->table->appendCell('name')
            ->addHeaderCss('max-width')
            ->addOnValue(function(Secret $obj, Cell $cell) {
                return sprintf('<a href="javascript:;" class="wiki-insert"
                    data-secret-hash="%s" data-secret-name="%s" data-secret-url="%s" title="Insert secret link">%s</a>',
                    $obj->hash, $obj->name, $obj->url, $obj->name);
            });

        $this->table->appendCell('userId')
            ->addOnValue(function(Secret $obj, Cell $cell) {
                return $obj->getUser()->nameShort ?? '';
            });

        $this->table->appendCell('permission')
            ->addOnValue(function(Secret $obj, Cell $cell) {
                return Secret::PERM_LIST[$obj->permission] ?? '';
            });


        // Add Filter Fields
        $this->table->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search name or category');

        // execute table
        $this->table->execute();

        $filter = $this->table->getDbFilter();
        $filter->replace([
            'publish' => true,
            'userId' => User::getAuthUser()->userId,
            'permission' => Page::PERM_PUBLIC
        ]);
        if (User::getAuthUser()->isMember()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_MEMBER];
        }
        if (User::getAuthUser()->isStaff()) {
            $filter['permission'] = [Page::PERM_PUBLIC, Page::PERM_MEMBER, Page::PERM_STAFF];
        }

        $list = Secret::findViewable($filter);
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
        $selectDialogId = self::CONTAINER_ID;

        $html = <<<HTML
<div class="modal fade" tabindex="-1" var="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Select Secret</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" var="content"></div>
      <div class="modal-footer" style="justify-content: space-between;" choice="show-create">
        <div>
          <button class="btn btn-sm btn-outline-primary btn-create-secret" type="button"
            hx-get="/component/secretEdit"
            hx-trigger="click queue:none"
            hx-target="body"
            hx-swap="beforeend">Create</button>
          <button class="btn btn-sm btn-outline-success btn-insert-list" type="button" var="user-id">Insert My List</button>
        </div>
        <div class="actions">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

<script>
jQuery(function($) {
    const dialog = '#{$selectDialogId}';
    const table  = '#{$this->table->getId()}';


    $(dialog).on('click', '.wiki-insert', function() {
        // insert existing secret
        let hash = $(this).data('secretHash');
        let name = $(this).data('secretName');
        $(document).trigger('selected.ss.modal', [hash, name]);
        $(dialog).modal('hide');
        return false;
    }).on('click', '.btn-create-secret', function() {
        $(dialog).modal('hide');
    }).on('click', '.btn-insert-list', function() {
        const editor = tinymce.activeEditor;
        let linkAttrs = {
          class: 'wk-secret-list',
          'wk-secret-list': $(this).data('user-id')
        };
        editor.insertContent(editor.dom.createHTML('div', linkAttrs, editor.dom.encode('{Secret Table Listing}')));
        $(dialog).modal('hide');
    });

    // open the dialog
    tkInit(table);
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
