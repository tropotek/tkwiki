<?php

namespace App\Component;

use App\Db\Secret;
use App\Db\User;
use Bs\Mvc\Form;
use Dom\Template;
use Tk\Form\Action\Link;
use Tk\Form\Action\Submit;
use Tk\Form\Field\Hidden;
use Tk\Form\Field\Input;
use Tk\Form\Field\InputButton;
use Tk\Form\Field\Password;
use Tk\Form\Field\Select;
use Tk\Uri;

class SecretEdit extends \Dom\Renderer\Renderer
{
    const string CONTAINER_ID = 'secret-edit-dialog';

    protected ?Form   $form       = null;
    protected ?Secret $secret     = null;
    protected array   $hxTriggers = [];


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()) return null;

        $secretId = intval($_REQUEST['secretId'] ?? 0);

        $this->secret = Secret::find($secretId);
        if (is_null($this->secret)) {
            $this->secret = new Secret();
            $this->secret->userId = User::getAuthUser()->userId;
        }

        $this->form = new Form($this->secret, 'secret-form');

        $tab = 'Details';
        $this->form->appendField(new Hidden('hash')); // needed for Htmx insert

        $this->form->appendField(new Input('name'))
            ->setGroup($tab);

        $this->form->appendField((new Select('permission', \App\Db\Secret::PERM_LIST))
            ->setGroup($tab)
            ->setStrict(true)
            ->setRequired()
            ->addFieldCss('col-sm-6')
            ->prependOption('-- Select --')
        );

        $this->form->appendField(new Input('url'))
            ->setGroup($tab)
            ->addFieldCss('col-sm-6');

        $this->form->appendField(new Input('username'))
            ->setGroup($tab)
            ->addFieldCss('col-sm-6');

        $this->form->appendField(new Password('password'))
            ->setGroup($tab)
            ->addFieldCss('col-sm-6');

        $url = Uri::create('/component/qrcodeReader');
        $this->form->appendField((new InputButton('otp', '<i class="fas fa-qrcode"></i>'))
            ->setBtnAttr([
                'hx-get' => $url,
                'hx-trigger' => 'click queue:none',
                'hx-target' => 'body',
                'hx-swap' => 'beforeend',
            ])
            ->addBtnCss('border-light-subtle is-dialog')
            ->setGroup($tab)
            ->setNotes('OTP secret passphrase. Generate 6 number code based on passphrase. <a href="https://en.wikipedia.org/wiki/One-time_password" target="_blank">More here</a>')
        );

        $this->form->appendField(new Link('cancel', Uri::create('#')))
            ->setAttr('data-bs-dismiss', 'modal')
            ->addCss('float-end');
        $this->form->appendField(new Submit('insert', [$this, 'onSubmit']))
            ->addCss('float-end');

        $load = $this->secret->unmapForm();
        $this->form->setFieldValues($load);

        $this->form->execute($_POST);

        if (!$this->form->isSubmitted()) {
            // Always set the htmx target and swap to end of the surrounding page <body>.
            header('HX-Retarget: body');
            header('HX-Reswap: beforeend');
        }

        // Send HX event headers
        if (count($this->hxTriggers)) {
            header(sprintf('HX-Trigger: %s', json_encode($this->hxTriggers)));
        }

        return $this->show();
    }

    public function onSubmit(Form $form, Submit $action): void
    {
        $values = $form->getFieldValues();
        $this->secret->mapForm($values);

        $form->addFieldErrors($this->secret->validate());
        if ($form->hasErrors()) {
            $this->hxTriggers['tkForm:onError'] = [
                'status' => 'err',
                'errors' => $form->getAllErrors()
            ];
            return;
        }

        $this->secret->save();

        // Trigger HX events
        $this->hxTriggers['tkForm:afterSubmit'] = [
            'target' => '#' . self::CONTAINER_ID,
            'status' => 'ok',
            'secretId' => $this->secret->secretId,
            'name' => $this->secret->name,
            'hash' => $this->secret->hash,
        ];
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setAttr('dialog', 'id', $this->getDialogId());
        $this->form->getRenderer()->getTemplate()->addCss('actions', 'mt-4 float-end');
        $this->form->getRenderer()->getTemplate()->removeCss('fields', 'g-3 mt-1')->addCss('fields', 'g-2');

        $template->appendTemplate('content', $this->form->htmxShow());

        return $template;
    }

    public function getDialogId(): string
    {
        return self::CONTAINER_ID;
    }

    public function __makeTemplate(): ?Template
    {
        $qrDialog = QrcodeReader::CONTAINER_ID;

        $html = <<<HTML
<div class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" var="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create Secret</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" var="content"></div>
        </div>
    </div>

<script>
jQuery(function($) {
    const qrDialog = '#{$qrDialog}';
    const dialog   = '#{$this->getDialogId()}';
    const form     = '#{$this->form->getId()}';


    $(document).on('htmx:afterSettle', dialog, function(e) {
        tkInit(form);
    });

    // open the dialog as soon as HTMX settles
    tkInit(form);
    $(dialog).modal('show');

    // put focus field when dialog shows
    $(dialog).on('shown.bs.modal', function() {
        $(dialog).data('detatch', true);
        setTimeout(function() { $('input:not(:hidden), textarea, select', dialog).first().focus(); }, 0);
    });

    // catch dialog finished handling post request
    $('body').on('tkForm:afterSubmit', function(e) {
        $(document).trigger('selected.ss.modal', [e.detail.hash, e.detail.name]);
        $(dialog).modal('hide');
    });

    // remove the dialog element from the dom when it closes
    $(document).on('hidden.bs.modal', dialog, function() {
        // do not detatch dialog if flag is set to false
        if ($(dialog).data('detatch') !== false) {
            $(dialog).remove();
        }
    });

    $('.fld-otp button', form).on('click', function(e) {
        $(dialog).data('detatch', false);
        $(dialog).modal('hide');
    });

    // QR reader dialog events
    $(document).on('qrcode-copy', function (e, code) {
      $('[name=otp]', form).val(code);
    });

    $(document).on('hidden.bs.modal', qrDialog, function() {
        // re-open edit on qr dialog close
        $(dialog).modal('show');
    });

});
</script>
</div>
HTML;
        return Template::load($html);
    }

}
