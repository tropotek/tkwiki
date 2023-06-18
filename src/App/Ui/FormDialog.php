<?php
namespace App\Ui;

use App\Form\Secret;
use Bs\Ui\Dialog;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;

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
 * @todo We need to make this re-usable for any form if possible....?????
 */
class FormDialog extends Dialog
{

    protected Secret $form;

    public function __construct(Secret $form, string $title, string $dialogId = '')
    {
        $this->form = $form;
        parent::__construct($title, $dialogId);
        $this->addCss('modal-lg');
    }

    public function init()
    {
        $this->getOnInit()->execute($this);
    }

    public function execute(Request $request)
    {
        $this->form->doDefault($request, $request->query->getInt('secret_id'));
        $this->getOnExecute()->execute($this, $request);
    }

    public function show(): ?Template
    {
        $this->setContent($this->form->show());
        $template = parent::show();

        $dialogId = $this->getId();
        $formId = $this->form->getForm()->getId();
        $js = <<<JS
jQuery(function($) {
    let formId = '#{$formId}';

    function init() {
        $('form.tk-form').each(function () {
            let form = $(this);
            let dialog = form.closest('.modal');
            $(formId+'-cancel', form).on('click', function () {
                dialog.modal('hide');
                $(formId+'-select-dialog').modal('show');
                return false;
            });
        });
    }
    init();
    $('body').on(EVENT_INIT_FORM, init);

    $('#{$dialogId}').on('show.bs.modal', function () {
        clearForm(this);
    });

});
JS;
        $template->appendJs($js);

        return $template;
    }

}