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

    protected ?Form $form = null;
    protected array $hxEvents = [];
    protected ?Secret $secret = null;


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()) return null;

        $secretId = intval($_GET['secretId'] ?? $_POST['secretId'] ?? 0);

        $this->secret = Secret::find($secretId);
        if (is_null($this->secret)) {
            $this->secret = new Secret();
            $this->secret->userId = User::getAuthUser()->userId;
        }

        $this->form = new Form($this->secret, 'secret-form');
        $this->form->setAction('');
        $this->form->setAttr('hx-post', Uri::create('/component/secretEdit'));
        $this->form->setAttr('hx-swap', 'outerHTML');
        $this->form->setAttr('hx-target', "#{$this->form->getId()}");
        $this->form->setAttr('hx-select', "#{$this->form->getId()}");


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

        $this->form->appendField((new InputButton('otp', '<i class="fas fa-qrcode"></i>'))
            ->setBtnAttr([
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#' . QrcodeReader::CONTAINER_ID,
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

        // Send HX event headers
        if (count($this->hxEvents)) {
            header(sprintf('HX-Trigger: %s', json_encode($this->hxEvents)));
        }

        return $this->show();
    }

    public function onSubmit(Form $form, Submit $action): void
    {
        $values = $form->getFieldValues();
        $this->secret->mapForm($values);

        $form->addFieldErrors($this->secret->validate());
        if ($form->hasErrors()) {
            $this->hxEvents['tkForm:onError'] = [
                'status' => 'err',
                'errors' => $form->getAllErrors()
            ];
            return;
        }

        $this->secret->save();

        // Trigger HX events
        $this->hxEvents['tkForm:afterSubmit'] = [
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

        $template->appendTemplate('content', $this->form->show());

        return $template;
    }

    public function getDialogId(): string
    {
        return self::CONTAINER_ID;
    }

    public function __makeTemplate(): ?Template
    {
        $baseUrl = Uri::create()->toString();
        $qrDialog = QrcodeReader::CONTAINER_ID;

        $html = <<<HTML
<div>
  <div class="modal fade" var="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Create Secret</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" var="content"></div>
      </div>
    </div>
  </div>

  <div hx-get="/component/qrcodeReader" hx-trigger="load" hx-swap="outerHTML"></div>

<script>
  jQuery(function($) {
    const qrDialog = '#{$qrDialog}';
    const dialog   = '#{$this->getDialogId()}';
    const form     = '#{$this->form->getId()}';
    const baseUrl  = '{$baseUrl}';

    // reload init form on load
    $(document).on('htmx:afterSettle', function(e) {
        if (!$(e.detail.elt).is(form)) return;
        if (e.detail.requestConfig.verb === 'get') {
            tkInit(e.detail.elt);
        }
    });

    // QR reader dialog events
    $(document).on('qrcode-copy', function (e, code) {
      $('[name=otp]', form).val(code);
    });
    $(document).on('hide.bs.modal', qrDialog, function(e) {
      // re-open edit on qr dialog close
      $(dialog).modal('show');
    });

    // reload page after successfull submit
    $(document).on('tkForm:afterSubmit', function(e) {
        if (!$(e.detail.elt).is(form)) return;
        $(dialog).modal('hide');
    });

    // reset form fields
    $(dialog).on('show.bs.modal', function(e) {
        if ($(this).data('refresh') === false) return;
        const url = new URL(baseUrl);
        if ($(e.relatedTarget).data('secretId')) {
            url.searchParams.set('secretId', $(e.relatedTarget).data('secretId'));
            $('.modal-title', dialog).text('Edit Secret');
        } else {
            $('.modal-title', dialog).text('Create Secret');
        }

        htmx.ajax('get', url.toString(), {
            select:    form,
            target:    form,
            swap:      'outerHTML'
        });
    });

  });
</script>
</div>
HTML;
        return Template::load($html);
    }

}
