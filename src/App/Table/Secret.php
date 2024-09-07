<?php
namespace App\Table;

use Bs\Table;
use Dom\Template;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tk\Form\Field\Checkbox;
use Tk\Form\Field\Input;
use Tk\Uri;
use Tt\Db;
use Tt\Table\Action\Delete;
use Tt\Table\Cell;
use Tt\Table\Cell\RowSelect;

class Secret extends Table
{

    public function init(): static
    {

        $rowSelect = RowSelect::create('id', 'userId');
        $this->appendCell($rowSelect);

        $this->appendCell('actions')
            ->addCss('text-nowrap text-center')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                $url = $obj->url;
                return <<<HTML
                    <a class="btn btn-sm btn-outline-primary" href="$url" title="Open in new tab" target="_blank"><i class="fa fa-globe"></i></a>
                HTML;
            });

        $this->appendCell('otp')
            ->addCss('text-nowrap text-center')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                if (empty($obj->otp)) return '';
                return <<<HTML
                    <a href="javascript:;" class="btn btn-sm btn-outline-success otp" data-id="{$obj->secretId}"><i class="fa fa-refresh"></i></a> <em>------</em>
                HTML;
            });

        $this->appendCell('name')
            ->addCss('text-nowrap')
            ->addHeaderCss('max-width')
            ->setSortable(true)
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                $url = Uri::create('/secretEdit', ['secretId' => $obj->secretId]);
                return sprintf('<a href="%s">%s</a>', $url, $obj->name);
            });

        $this->appendCell('userId')
            ->addCss('text-nowrap')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                return $obj->getUser()->getName();
            });

        $this->appendCell('permission')
            ->addCss('text-nowrap')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                return $obj->getPermissionLabel();
            });

        $this->appendCell('created')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addOnValue('\Tt\Table\Type\DateFmt::onValue');


        // Add Filter Fields
        $this->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search: name');

        $this->getForm()->appendField(new Checkbox('otp', ['otp' => 'y']));

        // init filter fields for actions to access to the filter values
        $this->initForm();

        // Add Table actions
        $this->appendAction(Delete::create($rowSelect))
            ->addOnDelete(function(Delete $action, array $selected) {
                foreach ($selected as $secret_id) {
                    $secret = \App\Db\Secret::find($secret_id);
                    if ($secret->canDelete($this->getAuthUser())) {
                        Db::delete('secret', compact('secret_id'));
                    }
                }
            });

        return $this;
    }

    public function execute(): static
    {
        if (isset($_POST['o'])) {
            $this->doOtp(intval($_POST['o']));
        }
        parent::execute();
        return $this;
    }

    #[NoReturn] public function doOtp(int $secretId): void
    {
        $response = new JsonResponse(['msg' => 'error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        $secret = \App\Db\Secret::find($secretId);
        if ($secret->canView($this->getFactory()->getAuthUser())) {
            $response = new JsonResponse(['otp' => $secret->genOtpCode()]);
        }
        $response->send();
        exit;
    }

    public function show(): ?Template
    {
        $renderer = $this->getRenderer();

        $js = <<<JS
jQuery(function($) {
  $('.tk-table table .otp').on('click', function (e) {
    let btn = $(this);
    var params = {'o': btn.data('id'), 'nolog': 'nolog'};
    $.post(document.location, params, function (data) {
      btn.next().text(data.otp);
      copyToClipboard(data.otp);
    });
    return false;
  });
});
JS;
        $renderer->getTemplate()->appendJs($js);

        return parent::show();
    }

}