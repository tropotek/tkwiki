<?php
namespace App\Controller;

use Bs\Mvc\ControllerPublic;
use Bs\Mvc\Form;
use Dom\Template;
use Tk\Alert;
use Tk\Form\Action\Link;
use Tk\Form\Action\Submit;
use Tk\Form\Field\Input;
use Tk\Form\Field\Textarea;
use Tk\Uri;

/**
 * This is only an example contact form.
 *
 * For commercial sites you should redirect to a "thank you" page or new thank you content template
 * showing a message rather than only an alert message.
 * Most clients prefer this type of
 *
 */
class Contact extends ControllerPublic
{

    protected ?Form $form = null;


    public function doDefault(): void
    {
        $this->getPage()->setTitle('Contact Us');

        $this->form = new Form();

        $this->form->appendField(new Input('name'));
        $this->form->appendField(new Input('email'))->setType('email');
        $this->form->appendField(new Input('phone'));
        $this->form->appendField(new Textarea('message'));

        $this->form->appendField(new Submit('send', [$this, 'onSubmit']));
        $this->form->appendField(new Link('cancel', Uri::create()));

        $this->form->setFieldValues($this->getRegistry()->all());

        $this->form->execute($_POST);

    }

    public function onSubmit(Form $form, Submit $action): void
    {
        if (!$form->getFieldValue('name')) {
            $form->addFieldError('name', 'Please enter a valid name.');
        }
        if (!filter_var($form->getFieldValue('email'), FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('email', 'Please enter a valid email.');
        }
        if (!$form->getFieldValue('message')) {
            $form->addFieldError('message', 'Please enter a valid message.');
        }

        if ($form->hasErrors()) return;

        $message = $this->getFactory()->createMessage();
        $message->addTo($form->getFieldValue('email'));
        $message->setSubject($this->getRegistry()->getSiteName() . ' Contact Request');
        $content = <<<HTML
<p>
Dear {name},
</p>
<p>
Email: {email}<br/>
Phone: {phone}<br/>
</p>
<p>Message:<br/>
  {message}
</p>
HTML;
        $message->setContent($content);
        $message->replace($form->getFieldValues());
        $this->getFactory()->getMailGateway()->send($message);

        Alert::addSuccess('Message Sent successfully');
        $action->setRedirect(Uri::create());
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());

        $this->form->getRenderer()->addFieldCss('mb-3');
        $template->appendTemplate('content', $this->form->show());

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-envelope"></i> </div>
    <div class="card-body" var="content"></div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}


