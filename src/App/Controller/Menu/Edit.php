<?php
namespace App\Controller\Menu;

use App\Db\MenuItem;
use App\Db\MenuItemMap;
use App\Db\PageMap;
use App\Db\User;
use App\Helper\PageSelect;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Db\Tool;
use Tk\Exception;
use Tk\Uri;

/**
 *
 * @see https://github.com/ilikenwf/nestedSortable/tree/v2.0.0
 */
class Edit extends PageController
{


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Edit Menu');
        $this->setAccess(User::PERM_SYSADMIN | User::PERM_EDITOR);
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
            $page = PageMap::create()->find($pageId);
            if (!$page) {
                throw new Exception('Invalid page id: ' . $pageId);
            }
            $item = new MenuItem();
            $item->setPageId($pageId);
            $item->setName($page->getTitle());
            $item->save();
            $item->setOrderId($item->getId());
            $item->save();

            $data = [
                'menuItemId' => $item->getId(),
                'pageId' => $page->getId(),
                'name' => $item->getName()
            ];
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
                MenuItemMap::create()->updateItem((int)$item['id'], (int)$item['parent_id'], (int)$orderId, trim($item['name']));
            }
            $data = [ 'status' => 'ok' ];
            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function doDelete(Request $request): JsonResponse
    {
        try {
            $itemId = $request->request->getInt('id');
            $item = MenuItemMap::create()->find($itemId);
            $item?->delete();
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
  background: #fff;
  padding: 2px;
}
.menu-box ul.menu-list > li a {
  background: #fff;
  font-size: 14px;
  text-decoration: none;
}
.menu-box ul.menu-list > li i {
  cursor: move;
}
.menu-box ul.menu-list ul {
  margin-left: 20px;
  margin-top: 5px;
}
.menu-box ul.menu-list > li b {
  cursor: pointer;
}
CSS;
        $template->appendCss($css);


        $js = <<<JS
jQuery(function($) {

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
            $('button.btn-save-menu').prop('disabled', false);
		}
    });
    // Store menu item name into data attr
    $('li', sortable).each(function () {
        $(this).data('name', $('a', this).html());
    });

    $('li a', sortable).on('keyup', function (e) {
        if (e.which === 13) $(this).blur();
        $('button.btn-save-menu').prop('disabled', false);
    }).on('blur', function () {
        $(this).parent().data('name', $(this).html());
    });

    // Delete menu item
    $('li b.fa-trash', sortable).on('click', function () {
        if (confirm('Delete this menu item.')) {
            $(this).parent().remove();
            $.post(location.href, {action: 'delete', id: $(this).parent().data }, function(data) { });
        }
    });

    // Save menu items
    $('button.btn-save-menu').on('click', function() {
        let sortable = $('.sortable');
        let result = sortable.nestedSortable('toArray', {startDepthCount: 0});
		for (item of result) {
            if (!item.id) continue;
            item.name = $('#item-'+item.id+' a', sortable).html();
            item.itemId = $('#item-'+item.id, sortable).data('itemId')+'';
		}
        result.shift();
        $.post(location.href, {action: 'update', list: result}, function(data) { });

        $('button.btn-save-menu').prop('disabled', true);
    });

    // Add page dialog
    $('td a.wiki-insert', '#page-select-dialog').on('click', function (e) {
        e.stopPropagation();
        let page = $(this).data();

        // Create new menu item and get item id returned from server
        $.post(location.href, {action: 'create', pageId: page.pageId}, function(data) {
            let li = $(liTpl);
            li.attr('id', 'item-' + data.menuItemId);
            li.data('itemId', data.menuItemId);
            li.data('pageId', data.pageId);
            $('a', li).html(data.name);
            sortable.append(li);

            $('#page-select-dialog').modal('hide');
        });
        return false;
    });

});
JS;
        $template->appendJs($js);

        $ul = $this->showMenu();
        $template->appendHtml('menu-box', $ul);
        $template->setVisible('menu-box');

        return $template;
    }

    private function showMenu(int $parentId = 0): string
    {
        $items = MenuItemMap::create()->findByParentId($parentId, Tool::create('order_id'));
        $css = '';
        if ($parentId == 0) {
            $css = ' class="menu-list sortable"';
        }
        $ul = sprintf('<ul%s>', $css);
        foreach ($items as $item) {
            $iul = '';
            if ($item->hasChildren()) {
                $iul = $this->showMenu($item->getId());
            }
            $ul .= <<<HTML
<li id="item-{$item->getId()}" data-item-id="{$item->getId()}" data-page-id="{$item->getPageId()}">
  <i class="fa fa-fw fa-ellipsis-vertical"></i>
  <a href="javascript:;" contentEditable="true">{$item->getName()}</a>
  <b class="fa fa-fw fa-trash text-danger float-end"></b>
  {$iul}
</li>
HTML;
        }
        $ul .= '</ul>';

        return $ul;
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

      <div class="menu-box" choice="menu-box"></div>

      <br/>
      <button class="btn btn-outline-success btn-add-page" data-bs-toggle="modal" data-bs-target="#page-select-dialog">Add Page</button>
      <button class="btn btn-outline-primary btn-save-menu" disabled>Save Menu</button>
    </div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}