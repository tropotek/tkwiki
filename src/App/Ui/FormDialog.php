<?php
namespace App\Ui;

use App\Form\Secret;
use Bs\Traits\SystemTrait;
use Bs\Ui\Dialog;
use Dom\Template;

/**
 * This class uses the bootstrap dialog box model
 * @link https://getbootstrap.com/docs/5.3/components/modal/
 *
 * To create the dialog:
 *
 *   $dialog = FormDialog::create('myDialog', 'My Dialog Title');
 *   $dialog->setOnInit(function ($dialog) { ... });
 *   $dialog->setOnShow(function ($dialog) { $template = $dialog->getTemplate(); });
 *   ...
 *   $dialog->init();                   // Optional
 *   ...
 *   $dialog->execute($request);        // Optional
 *   ...
 *   $template->appendBodyTemplate($dialog->show());
 *
 * To add a close button to the footer:
 *
 *    $dialog->getButtonList()->append(\Tk\Ui\Button::createButton('Close')->setAttr('data-dismiss', 'modal'));
 *
 * Launch Button:
 *
 *    <a href="#" data-bs-toggle="modal" data-bs-target="#{id}"><i class="fa fa-info-circle"></i> {title}</a>
 *
 *    $template->setAttr('modelBtn', 'data-bs-toggle', 'modal');
 *    $template->setAttr('modelBtn', 'data-bs-target', '#'.$this->dialog->getId());
 *
 * @todo review this class
 */
class FormDialog extends Dialog
{
    use SystemTrait;

    protected Secret $form;

    public function __construct(Secret $form, string $title, string $dialogId = '')
    {
        $this->form = $form;
        parent::__construct($title, $dialogId);
        $this->addCss('modal-lg');
        $this->setAttr('data-bs-backdrop', 'static');
    }

    public function init(): void
    {
        $this->getOnInit()->execute($this);
    }

    public function execute(): void
    {
        $secret = new \App\Db\Secret();
        $secret->userId = $this->getAuthUser()->userId;
        if ($_GET['secretId'] ?? false) {
            $secret = \App\Db\Secret::find((int)$_GET['secretId']);
        }

        $this->form->setModel($secret);
        $this->form->execute($_POST);

        $this->getOnExecute()->execute($this);
    }

    public function show(): ?Template
    {
        $this->setContent($this->form->show());
        $template = parent::show();

        $dialogId = $this->getId();
        //$formId = $this->form->getForm()->getId();
        $js = <<<JS
jQuery(function($) {
    let dialogId = '#{$dialogId}';

    $('form.tk-form', dialogId).each(function () {
        let form = $(this);
        let formId = '#' + $(this).attr('id');
        let dialog = form.closest('.modal');
        $(formId+'-cancel', form).on('click', function () {
            dialog.modal('hide');
            $(formId+'-select-dialog').modal('show');
            return false;
        });
    });

    $(dialogId).on('show.bs.modal', function () {
        clearForm(this);
    });

});
JS;
        $template->appendJs($js);

        return $template;
    }

}
