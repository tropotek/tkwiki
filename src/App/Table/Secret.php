<?php
namespace App\Table;

use App\Db\SecretMap;
use Dom\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tk\Alert;
use Tk\Traits\SystemTrait;
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
        $secret = SecretMap::create()->find($request->query->getInt('o', 0));
        if ($secret) {
            $response = new JsonResponse(['otp' => $secret->genOtpCode()]);
        }
        $response->send();
        exit;
    }

    public function doDefault(Request $request)
    {
        $editUrl = Uri::create('/secretEdit');

        if ($request->query->getInt('o')) {
            $this->doOtp($request);
        }

        $this->getTable()->appendCell(new Cell\Checkbox('id'));
        $this->getTable()->appendCell(new Cell\Text('otp'))
            ->addOnValue(function (Cell\Text $cell, mixed $value) {
                return '';
            })
            ->addOnShow(function (Cell\Text $cell, string $html) {
                /** @var \App\Db\Secret $obj */
                $obj = $cell->getRow()->getData();
                if ($obj->getOtp()) {
                    $html = sprintf('<button class="btn btn-sm btn-outline-success otp" data-auth-id="%s"><i class="fa fa-refresh"></i></button> <strong class="otp2">------</strong>',
                        $obj->getId());
                }
                return $html;
            });

        $this->getTable()->appendCell(new Cell\Text('userId'));
        $this->getTable()->appendCell(new Cell\Text('name'))->addCss('key')->setUrl($editUrl);
        $this->getTable()->appendCell(new Cell\Text('permission'));
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