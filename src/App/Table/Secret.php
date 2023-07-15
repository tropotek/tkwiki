<?php
namespace App\Table;

use App\Db\SecretMap;
use Bs\Table\ManagerInterface;
use Dom\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Db\Mapper\Result;
use Tk\Db\Tool;
use Tk\Ui\Link;
use Tk\Uri;
use Tk\Form\Field;
use Tk\Table\Cell;
use Tk\Table\Action;

class Secret extends ManagerInterface
{

    public function initCells(): void
    {
        $editUrl = Uri::create('/secretEdit');

        $this->appendCell(new Cell\Checkbox('secretId'));
        $this->appendCell(new Cell\Text('actions'))
            ->addOnShow(function (Cell\Text $cell, string $html) {
            $cell->addCss('text-nowrap text-center');
            $obj = $cell->getRow()->getData();

            $template = $cell->getTemplate();
            $btn = new Link('View');
            $btn->setText('');
            $btn->setAttr('title', 'Open in new tab');
            $btn->setAttr('target', '_blank');
            $btn->setIcon('fa fa-globe');
            $btn->addCss('btn btn-sm btn-outline-primary');

            if ($obj->getUrl()) {
                $btn->setUrl($obj->getUrl());
                $template->appendTemplate('td', $btn->show());
                $template->appendHtml('td', '&nbsp;');
            }

            return '';
        });
        $this->appendCell(new Cell\Text('otp'))
            ->addOnValue(function (Cell\Text $cell, mixed $value) {
                return '';
            })
            ->addOnShow(function (Cell\Text $cell, string $html) {
                /** @var \App\Db\Secret $obj */
                $obj = $cell->getRow()->getData();
                if ($obj->getOtp()) {
                    $html = sprintf('<a href="javascript:;" class="btn btn-sm btn-outline-success otp" data-id="%s"><i class="fa fa-refresh"></i></a> <em>------</em>',
                        $obj->getId());
                }
                return $html;
            });

        $this->appendCell(new Cell\Text('name'))->addCss('key')
            ->setUrlProperty('secretId')
            ->setUrl($editUrl);

        $this->appendCell(new Cell\Text('userId'))
            ->addOnValue(function (Cell\Text $cell, mixed $value) {
                /** @var \App\Db\Secret $obj */
                $obj = $cell->getRow()->getData();
                return $obj->getUser()?->getName() ?? $value;
            });

        $this->appendCell(new Cell\Text('permission'))
            ->addOnValue(function (Cell\Text $cell, mixed $value) {
                /** @var \App\Db\Secret $obj */
                $obj = $cell->getRow()->getData();
                return $obj->getPermissionLabel();
            });

        $this->appendCell(new Cell\Text('created'));


        // Filters
        $this->getFilterForm()->appendField(new Field\Input('search'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(new Action\Button('Create'))->setUrl($editUrl);
        $this->appendAction(new Action\Delete('delete', 'secretId'));
        $this->appendAction(new Action\Csv('csv', 'secretId'))->addExcluded('actions');

    }

    public function execute(Request $request): static
    {
        if ($request->request->getInt('o')) {
            $this->doOtp($request);
        }

        return parent::execute($request);
    }

    public function findList(array $filter = [], ?Tool $tool = null): null|array|Result
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterForm()->getFieldValues(), $filter);

        $user = $this->getFactory()->getAuthUser();
        if ($user?->isStaff()) {
            $filter['permission'] = [\App\Db\Secret::PERM_USER, \App\Db\Secret::PERM_STAFF];
            $filter['author'] = $user->getUserId();
        }

        $list = SecretMap::create()->findFiltered($filter, $tool);
        $this->setList($list);
        return $list;
    }

    public function doOtp(Request $request)
    {
        $response = new JsonResponse(['msg' => 'error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        /** @var \App\Db\Secret $secret */
        $secret = SecretMap::create()->find($request->request->getInt('o', 0));
        if ($secret && $secret->canView($this->getFactory()->getAuthUser())) {
            $response = new JsonResponse(['otp' => $secret->genOtpCode()]);
        }
        $response->send();
        exit;
    }

    public function show(): ?Template
    {
        $renderer = $this->getTableRenderer();
        $this->getRow()->addCss('text-nowrap');
        $this->showFilterForm();

        $js = <<<JS
jQuery(function($) {
  $('.tk-table table .otp').on('click', function (e) {
    let btn = $(this);
    var params = {'o': btn.data('id'), 'nolog': 'nolog'};
    //var params = {'o': btn.data('id')};
    $.post(document.location, params, function (data) {
      btn.next().text(data.otp);
      copyToClipboard(data.otp);
    });
    return false;
  });
});
JS;
        $renderer->getTemplate()->appendJs($js);

        return $renderer->show();
    }

}