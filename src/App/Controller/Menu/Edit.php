<?php
namespace App\Controller\Menu;

use App\Db\MenuItem;
use App\Db\MenuItemMap;
use App\Db\PageMap;
use App\Db\Permissions;
use App\Helper\PageSelect;
use Bs\PageController;
use Bs\Ui\Dialog;
use Dom\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Exception;
use Tk\Uri;

/**
 * @see https://github.com/ilikenwf/nestedSortable/tree/v2.0.0
 */
class Edit extends PageController
{

    public function __construct()
    {
        parent::__construct();
        $this->getPage()->setTitle('Edit Menu');
        $this->setAccess(Permissions::PERM_SYSADMIN | Permissions::PERM_EDITOR);
        $this->getCrumbs()->reset();
    }

    public function doDefault(Request $request)
    {
        switch ($request->request->get('action')) {
            case 'create':
                return $this->doCreate($request);
            case 'update':
                return $this->doUpdate($request);
            case 'delete':
                return $this->doDelete($request);
        }

        return $this->getPage();
    }

    public function doCreate(Request $request): JsonResponse
    {
        try {
            $pageId = $request->request->getInt('pageId');
            $type   = $request->request->getString('type', MenuItem::TYPE_ITEM);
            $name   = $request->request->getString('name', '');

            $item = new MenuItem();
            $item->type = $type;

            if ($type == MenuItem::TYPE_ITEM) {
                $page = PageMap::create()->find($pageId);
                if (!$page) {
                    throw new Exception('Invalid page id: ' . $pageId);
                }
                $item->pageId = $pageId;
                $item->setName($page->title);
            } elseif ($type == MenuItem::TYPE_DIVIDER) {
                $item->setName($name);
            } elseif ($type == MenuItem::TYPE_DROPDOWN) {
                $item->setName($name);
            }

            $item->save();
            $item->orderId = $item->menuItemId;
            $item->save();

            $data = [
                'menuItemId' => $item->menuItemId,
                'pageId' => $item->pageId,
                'name' => $item->name,
                'type' => $item->type,
            ];
            MenuItem::indexLinks();
            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function doUpdate(Request $request): JsonResponse
    {
        try {
            $list = $_POST['list'];
            if (!is_array($list)) {
                throw new Exception('cannot save menu');
            }
            foreach ($list as $orderId => $item) {
                if (empty($item['parent_id'])) $item['parent_id'] = null;
                MenuItem::updateItem((int)$item['id'], $item['parent_id'], (int)$orderId, trim($item['name']));
            }

            MenuItem::indexLinks();
            $data = [ 'status' => 'ok' ];
            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function doDelete(Request $request): JsonResponse
    {
        try {
            $menuItemId = $request->request->getInt('id');
            $item = MenuItemMap::create()->find($menuItemId);
            $item?->delete();
            MenuItem::indexLinks();
            return new JsonResponse([ 'status' => 'ok' ]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());
        //$template->setAttr('back', 'href', $this->getBackUrl());

        $dialog = new PageSelect();
        $template->appendBodyTemplate($dialog->show());

        $url = Uri::create('/html/assets/js/jquery.mjs.nestedSortable.js');
        $template->appendJsUrl($url);

        $css = <<<CSS
.menu-box {
  border: 1px solid #CCCCCC;
  margin-bottom: 15px;
  padding: 15px;
}
.menu-box ul {
  margin: 0;
  padding: 0;
  list-style: none;
}
.menu-box ul.menu-list li {
  display: block;
  margin-bottom: 5px;
  border: 1px solid #f1e8e2;
  background: #EFEFEF;
  padding: 2px;
}
.menu-box ul.menu-list li.mjs-nestedSortable-no-nesting {
  background: #fff;
}
.menu-box ul.menu-list > li a {
  /* background: #fff; */
  font-size: 14px;
  text-decoration: none;
}
.menu-box ul.menu-list > li i {
  cursor: move;
}
.menu-box ul.menu-list ul {
  margin-left: 20px;
  margin-top: 4px;
}
.menu-box ul.menu-list > li b {
  padding-top: 5px;
  cursor: pointer;
}
CSS;
        $template->appendCss($css);


        $js = <<<JS
jQuery(function($) {

    const liTpl = `
<li id="item-0" data-item-id="0" data-page-id="0">
  <i class="fa fa-fw fa-ellipsis-vertical"></i>
  <a href="javascript:;"></a>
  <b class="fa fa-fw fa-trash text-danger float-end"></b>
</li>`;

    // Setup the nested sortable plugin
    let sortable = $('.sortable').nestedSortable({
		forcePlaceholderSize: true,
		items: 'li',
		handle: 'i',
		placeholder: 'menu-highlight',
		listType: 'ul',
		maxLevels: 2,
		opacity: .6,
		relocate: function (a, b) {
            saveItem();
		}
    });

    // Store menu item name into data attr
    $('li', sortable).each(function () {
        $(this).data('name', $('a', this).html());
    });

    $(sortable).on('keyup', 'li a', function (e) {
        if (e.which === 13) $(this).blur();
    }).on('blur', 'li a', function () {
        $(this).parent().data('name', $(this).html());
    });

    // Delete menu item
    $(sortable).on('click', 'li b.fa-trash', function () {
        if (confirm('Delete this menu item.')) {
            $(this).parent().remove();
            $.post(location.href, {action: 'delete', id: $(this).parent().data('itemId') }, function(data) {  });
        }
    });

    // Save menu items
    function saveItem() {
        let result = sortable.nestedSortable('toArray', {startDepthCount: 0});
		for (item of result) {
            if (!item.id) continue;
            item.name = $('#item-'+item.id+' a', sortable).html();
            item.itemId = $('#item-'+item.id, sortable).data('itemId')+'';
		}
        result.shift();
        $.post(location.href, {action: 'update', list: result}, function(data) { });
    }

    // Add page dialog
    $('td a.wiki-insert', '#page-select-dialog').on('click', function (e) {
        e.stopPropagation();
        let page = $(this).data();

        // Create new menu item and get item id returned from server
        $.post(location.href, {action: 'create', pageId: page.pageId, type: 'item'}, function(data) {
            let li = $(liTpl);
            li.addClass('mjs-nestedSortable-no-nesting');
            li.attr('id', 'item-' + data.menuItemId);
            li.data('itemId', data.menuItemId);
            li.data('pageId', data.pageId);
            li.data('type', data.type);
            $('a', li).html(data.name);
            $('a', li).attr('contentEditable', 'true');
            sortable.append(li);
            $('button.btn-save-menu').prop('disabled', false);

            $('#page-select-dialog').modal('hide');
        });
        return false;
    });

    // Add dropdown item
    sortable.on('create-dropdown', function(obj, name) {
        // Create new menu item and get item id returned from server
        $.post(location.href, {action: 'create', pageId: 0, type: 'dropdown', name: name}, function(data) {
            let li = $(liTpl);
            li.addClass('dropdown');
            li.attr('id', 'item-' + data.menuItemId);
            li.data('itemId', data.menuItemId);
            li.data('pageId', '0');
            li.data('type', data.type);
            $('a', li).html(data.name);
            $('a', li).attr('contentEditable', 'true');
            sortable.append(li);
        });
    });

    // Add divider item
    $('.btn-add-divider').on('click', function() {
        // Create new menu item and get item id returned from server
        $.post(location.href, {action: 'create', pageId: 0, type: 'divider', name: '{divider}'}, function(data) {
            let li = $(liTpl);
            li.addClass('mjs-nestedSortable-no-nesting');
            li.attr('id', 'item-' + data.menuItemId);
            li.data('itemId', data.menuItemId);
            li.data('pageId', '0');
            li.data('type', data.type);
            $('a', li).html(data.name);
            sortable.append(li);
        });
    });

});
JS;
        $template->appendJs($js);

        $ul = $this->showMenu();
        $template->appendHtml('menu-box', $ul);
        $template->setVisible('menu-box');

        $this->showDropdownDialog();

        return $template;
    }

    private function showMenu(int $parentId = 0): string
    {
        $items = MenuItem::findByParentId($parentId);
        //$items = MenuItemMap::create()->findByParentId($parentId, Tool::create('order_id'));
        $css = '';
        if ($parentId == 0) {
            $css = ' class="menu-list sortable"';
        }
        $ul = sprintf('<ul%s>', $css);
        foreach ($items as $item) {

            if ($item->isType(MenuItem::TYPE_DROPDOWN)) {
                $iul = '';
                if ($item->hasChildren()) {
                    $iul = $this->showMenu($item->menuItemId);
                }
                $ul .= <<<HTML
<li class="dropdown" id="item-{$item->menuItemId}" data-item-id="{$item->menuItemId}" data-page-id="0" data-type="{$item->type}">
  <i class="fa fa-fw fa-ellipsis-vertical"></i>
  <a href="javascript:;" contentEditable="true">{$item->name}</a>
  <b class="fa fa-fw fa-trash text-danger float-end pt-1"></b>
  {$iul}
</li>
HTML;

            } elseif ($item->isType(MenuItem::TYPE_DIVIDER)) {
                $ul .= <<<HTML
<li id="item-{$item->getId()}" data-item-id="{$item->menuItemId}" data-page-id="0" data-type="{$item->type}" class="mjs-nestedSortable-no-nesting">
  <i class="fa fa-fw fa-ellipsis-vertical"></i>
  <a href="javascript:;" contentEditable="true">{$item->name}</a>
  <b class="fa fa-fw fa-trash text-danger float-end"></b>
</li>
HTML;

            } else {
                $ul .= <<<HTML
<li id="item-{$item->getId()}" data-item-id="{$item->menuItemId}" data-page-id="{$item->pageId}" data-type="{$item->type}" class="mjs-nestedSortable-no-nesting">
  <i class="fa fa-fw fa-ellipsis-vertical"></i>
  <a href="javascript:;" contentEditable="true">{$item->name}</a>
  <b class="fa fa-fw fa-trash text-danger float-end"></b>
</li>
HTML;
            }
        }
        $ul .= '</ul>';

        return $ul;
    }

    protected function showDropdownDialog(): void
    {
        $dialog = new Dialog('Create dropdown Item', 'create-dropdown-dialog');

        $dialog->addButton('Cancel')->addCss('btn btn-outline-secondary');
        $dialog->addButton('Create')->addCss('btn btn-outline-primary btn-create');

        $html = <<<HTML
<div>
   <div class="mb-3">
     <label for="create-dropdown-name" class="form-label">Select a name for the dropdown:</label>
     <input type="text" name="title" id="create-dropdown-name" class="form-control" placeholder="Dropdown Name">
   </div>
</div>
HTML;
        $dialog->setContent($html);
        $js = <<<JS
jQuery(function ($) {
    $('.btn-create', '#create-dropdown-dialog').on('click', function () {
        let name = $('#create-dropdown-name').val().trim();
        if (name) {
            $('.sortable').trigger('create-dropdown', [name]);
        }
        $('#create-dropdown-dialog').modal('hide');
    });

    $('#create-dropdown-dialog').on('show.bs.modal', function () {
        $('input', this).val('');
    })
    .on('shown.bs.modal', function () {
        $('input:first', this).focus();
    });
});
JS;
        $this->getTemplate()->appendJs($js);

        $this->getTemplate()->appendBodyTemplate($dialog->show());
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <div class="card mb-3">
    <div class="card-header"><i class="fa fa-cogs"></i> Actions</div>
    <div class="card-body" var="actions">
      <a href="/" title="Back" class="btn btn-outline-secondary" var="back"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-bars"></i> </div>
    <div class="card-body" var="content">

      <div class="menu-actions mb-3">
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            Add Item
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item btn-add-page" href="javascript:;" data-bs-toggle="modal" data-bs-target="#page-select-dialog">Add Page</a></li>
            <li><a class="dropdown-item btn-add-dropdown" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create-dropdown-dialog">Add Dropdown</a></li>
            <li><a class="dropdown-item btn-add-divider" href="javascript:;">Add Divider</a></li>
          </ul>
        </div>
        &nbsp;
<!--        <button class="btn btn-outline-success btn-sm btn-save-menu" disabled>Save Menu</button>-->
      </div>

      <div class="menu-box" choice="menu-box"></div>

      <p><small><em>Note: Dividers only work in dropdown menus.</em></small></p>

    </div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}