<?php
namespace App\Component;

use App\Db\Content;
use App\Db\Page;
use App\Db\User;
use Bs\Mvc\ComponentInterface;
use Dom\Template;
use Tk\Date;

class MenuAddDropdown extends \Dom\Renderer\Renderer implements ComponentInterface
{
    const string CONTAINER_ID = 'dlg-menu-add-dropdown';


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()) return null;

        // Always set the htmx target and swap to end of the surrounding page <body>.
        header('HX-Retarget: body');
        header('HX-Reswap: beforeend');

        return $this->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setAttr('dialog', 'id', self::CONTAINER_ID);


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
                    <h4 class="modal-title">Create Dropdown Item</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body select-table" var="content">
                   <div class="mb-3">
                     <label for="create-dropdown-name" class="form-label">Select a name for the dropdown:</label>
                     <input type="text" name="title" id="create-dropdown-name" class="form-control" placeholder="Dropdown Name">
                   </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-create">Create</button>
                </div>
              </div>
            </div>
        </div>
    </div>

<script>
jQuery(function($) {
    const dialog = '#{$dialogId}';

    $(dialog).modal('show');

    $('.btn-create', dialog).on('click', function() {
        $(document).trigger('create-dropdown', [$('[name=title]', dialog).val()]);
        $(dialog).modal('hide');
    });

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
