<?php
namespace App\Table;

use App\Db\SecretMap;
use Dom\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Db\Mapper\Result;
use Tk\Traits\SystemTrait;
use Tk\Ui\Link;
use Tk\Uri;
use Tk\Form;
use Tk\Form\Field;
use Tk\FormRenderer;
use Tk\Table;
use Tk\Table\Cell;
use Tk\Table\Action;
use Tk\TableRenderer;

class Secret
{
    use SystemTrait;

    protected Table $table;

    protected ?Form $filter = null;


    public function __construct()
    {
        $this->table  = new Table();
        $this->filter = new Form($this->table->getId() . '-filters');
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

    public function doDefault(Request $request)
    {
        $editUrl = Uri::create('/secretEdit');

        if ($request->request->getInt('o')) {
            $this->doOtp($request);
        }

        $this->getTable()->appendCell(new Cell\Checkbox('id'));
        $this->getTable()->appendCell(new Cell\Text('actions'))
            ->addOnShow(function (Cell\Text $cell, string $html) use ($editUrl) {
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
        $this->getTable()->appendCell(new Cell\Text('otp'))
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

        $this->getTable()->appendCell(new Cell\Text('name'))->addCss('key')->setUrl($editUrl);

        $this->getTable()->appendCell(new Cell\Text('userId'))
            ->addOnValue(function (Cell\Text $cell, mixed $value) {
                /** @var \App\Db\Secret $obj */
                $obj = $cell->getRow()->getData();
                return $obj->getUser()?->getName() ?? $value;
            });
        $this->getTable()->appendCell(new Cell\Text('permission'))
            ->addOnValue(function (Cell\Text $cell, mixed $value) {
                /** @var \App\Db\Secret $obj */
                $obj = $cell->getRow()->getData();
                return $obj->getPermissionLabel();
            });
        $this->getTable()->appendCell(new Cell\Text('created'));


        // Filters
        $this->getFilter()->appendField(new Field\Input('search'))->setAttr('placeholder', 'Search');

        // load filter values
        $this->getFilter()->setFieldValues($this->getTable()->getTableSession()->get($this->getFilter()->getId(), []));
        $this->getFilter()->appendField(new Form\Action\Submit('Search', function (Form $form, Form\Action\ActionInterface $action) {
            $this->getTable()->getTableSession()->set($this->getFilter()->getId(), $form->getFieldValues());
            Uri::create()->redirect();
        }))->setGroup('');
        $this->getFilter()->appendField(new Form\Action\Submit('Clear', function (Form $form, Form\Action\ActionInterface $action) {
            $this->getTable()->getTableSession()->set($this->getFilter()->getId(), []);
            Uri::create()->redirect();
        }))->setGroup('')->addCss('btn-outline-secondary');
        // execute filter form
        $this->getFilter()->execute($request->request->all());


        // Actions
        if ($this->getConfig()->isDebug()) {
            $this->getTable()->appendAction(new Action\Link('reset', Uri::create()->set(Table::RESET_TABLE, $this->getTable()->getId()), 'fa fa-retweet'))
                ->setLabel('')
                ->setAttr('data-confirm', 'Are you sure you want to reset the Table`s session?')
                ->setAttr('title', 'Reset table filters and order to default.');
        }
        $this->getTable()->appendAction(new Action\Button('Create'))->setUrl($editUrl);
        $this->getTable()->appendAction(new Action\Delete());
        $this->getTable()->appendAction(new Action\Csv())->addExcluded('actions');

    }

    public function execute(Request $request, ?Result $list = null): void
    {
        // Query
        if (!$list) {
            $tool = $this->getTable()->getTool();
            $filter = $this->getFilter()->getFieldValues();
            $user = $this->getFactory()->getAuthUser();
            if ($user?->isAdmin()) {
                ; // Search all
            } elseif ($user?->isStaff()) {
                $filter['permission'] = [\App\Db\Secret::PERM_USER, \App\Db\Secret::PERM_STAFF];
                $filter['author'] = $user->getId();
            }
            $list = \App\Db\SecretMap::create()->findFiltered($filter, $tool);
        }
        $this->getTable()->setList($list);

        $this->getTable()->execute($request);
    }

    public function show(): ?Template
    {
        $renderer = new TableRenderer($this->getTable());
        $this->getTable()->getRow()->addCss('text-nowrap');
        $this->getTable()->addCss('table-hover');

        if ($this->getFilter()) {
            $this->getFilter()->addCss('row gy-2 gx-3 align-items-center');
            $filterRenderer = FormRenderer::createInlineRenderer($this->getFilter());
            $renderer->getTemplate()->appendTemplate('filters', $filterRenderer->show());
            $renderer->getTemplate()->setVisible('filters');
        }
        $js = <<<JS
jQuery(function($) {

  $('.tk-table table .otp').on('click', function (e) {
    let btn = $(this);
    //var params = {'o': btn.data('id'), 'nolog': 'nolog'};
    var params = {'o': btn.data('id')};
    $.post(document.location, params, function (data) {
      btn.next().text(data.otp);
      copyToClipboard(btn.next().get(0));
    });
    return false;
  });

});
JS;
        $renderer->getTemplate()->appendJs($js);

        return $renderer->show();
    }


    public function getTable(): Table
    {
        return $this->table;
    }

    public function getFilter(): ?Form
    {
        return $this->filter;
    }
}