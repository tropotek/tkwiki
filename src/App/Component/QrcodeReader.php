<?php
namespace App\Component;

use App\Db\User;
use Dom\Template;
use Tk\Uri;
use Zxing\QrReader;

class QrcodeReader extends \Dom\Renderer\Renderer
{
    const string CONTAINER_ID = 'qr-reader-dialog';

    protected string  $image      = '';
    protected string  $code       = '';
    protected array   $hxTriggers = [];


    public function doDefault(): ?Template
    {
        if (!User::getAuthUser()) return null;

        $action = trim($_POST['action'] ?? '');

        if ($action == 'read') {
            $this->image = trim($_POST['qrimage'] ?? '');
            $ex = explode(',', $this->image, 2);
            $img = base64_decode($ex[1] ?? '');
            $qrcode = new QrReader($img, QrReader::SOURCE_TYPE_BLOB);
            $this->code = $qrcode->text();
        }

        // Send HX event headers
        if (count($this->hxTriggers)) {
            header(sprintf('HX-Trigger: %s', json_encode($this->hxTriggers)));
        }

        return $this->show();
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setAttr('dialog', 'id', $this->getDialogId());

        if (!empty($this->image)) {
            $template->setAttr('qr-image-preview', 'src', $this->image);
            $template->setAttr('qr-image-preview', 'data-code', $this->code);
            $template->setVisible('qr-image-preview');
        }

        return $template;
    }

    public function getDialogId(): string
    {
        return self::CONTAINER_ID;
    }

    public function __makeTemplate(): ?Template
    {
        $baseUrl = Uri::create()->toString();

        $html = <<<HTML
<div class="modal fade" tabindex="-1" aria-hidden="true" var="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">QR-Code Reader</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Past or select an image to read the QR code</p>
                <div class="text-center qr-image mt-2 mb-2" id="qr-reader-wrapper">
                    <img src="#" id="qr-image-preview" style="min-width: 50%;" choice="qr-image-preview" />
                </div>

                <div class="mt-2 mb-2">
                    <input type="file" class="form-control" accept=".jpg,.png,.gif" name="qr-image" />
                </div>
                <div class="mt-2 mb-2">
                    <div class="input-group input-group-merge">
                      <input type="text" class="form-control" value="" name="decoded" placeholder="QR code" readonly id="fid-qr-code" />
                      <a href="javascript:;" class="btn btn-white border-light-subtle btn-copy disabled" type="button" title="Click to use code">Copy</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
jQuery(function($) {
    const dialog = '#{$this->getDialogId()}';
    const baseUrl = '{$baseUrl}';

    $(document).on('htmx:afterSettle', dialog, function(e) {
        let code = $('#qr-image-preview', dialog).data('code');
        $('#fid-qr-code', dialog).val(code);
        if (code) {
            $('.btn-copy', dialog).removeClass('disabled');
        }
    });

    // open the dialog as soon as HTMX settles
    $(dialog).modal('show');

    // remove the dialog element from the dom when it closes
    $(dialog).on('hidden.bs.modal', function() {
        $(dialog).remove();
    });

    $(dialog).on('show.bs.modal', function(e) {
        // disable refresh for dialog
        if ($(e.relatedTarget).is('.is-dialog')) {
            let callingDialog = $(e.relatedTarget).closest('.modal');
            callingDialog.data('refresh', false);
        }
    });

    $('.btn-copy', dialog).on('click', function() {
        // Copy text to clipboard
        copyToClipboard($('#fid-qr-code', dialog).val());

        // hide this dialog
        $(dialog).modal('hide');

        // trigger an event to allow external forms access to the qr-code
        $(document).trigger('qrcode-copy', [$('#fid-qr-code', dialog).val()]);
    });

    $('[name="qr-image"]', dialog).on('change', function() {
        let input = $(this).get(0);
        if (input.files && input.files[0]) {
            requestCode(input.files[0]);
        }
    });

    $('.modal-body', dialog).on('paste', function(e) {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (const item of items) {
            if (item.type.indexOf('image') !== -1) {
                const blob = item.getAsFile();
                requestCode(blob);
                return;
            }
        }
    });

    function requestCode(blob) {
        const reader = new FileReader();
        reader.onload = function (e) {
            htmx.ajax('POST', baseUrl, {
                source: '#qr-reader-wrapper',
                select: '#qr-reader-wrapper',
                target: '#qr-reader-wrapper',
                swap:   'outerHTML',
                values: {
                    action: 'read',
                    qrimage: e.target.result,
                }
            });
        }
        reader.readAsDataURL(blob);
    }

});
</script>

<style>
div.qr-image {
    min-height: 250px;
    min-width: 100%;
    border: 1px solid #EFEFEF;
}
</style>
</div>
HTML;
        return Template::load($html);
    }

}
