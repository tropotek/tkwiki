<?php
namespace App\Controller;

use Tk\Request;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Contact extends \Bs\Controller\Iface
{

    /**
     * @var Form
     */
    protected $form = null;


    public function __construct()
    {
        $this->setPageTitle('Contact Us');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {

        $this->form = new Form('contactForm');

        $this->form->prependField(new Field\Input('name'));
        $this->form->prependField(new Field\Input('email'));

        $opts = new Field\Option\ArrayIterator(array('General', 'Services', 'Orders'));
        $this->form->prependField(new Field\Select('type[]', $opts));

        $this->form->prependField(new Field\File('attach[]', '/contact/' . date('d-m-Y') . '-___'));
        $this->form->prependField(new Field\Textarea('message'));

        if ($this->getConfig()->get('google.recaptcha.publicKey'))
            $this->form->prependField(new Field\ReCapture('capture', $this->getConfig()->get('google.recaptcha.publicKey'),
                $this->getConfig()->get('google.recaptcha.privateKey')));

        $this->form->prependField(new Event\Submit('send', array($this, 'doSubmit')));

        $this->form->execute();

    }

    /**
     * show()
     *
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
        $ren->show();

        return $template;
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function doSubmit($form)
    {
        $values = $form->getValues();
        /** @var Field\File $attach */
        $attach = $form->getField('attach');

        if (empty($values['name'])) {
            $form->addFieldError('name', 'Please enter your name');
        }
        if (empty($values['email']) || !filter_var($values['email'], \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('email', 'Please enter a valid email address');
        }
        if (empty($values['message'])) {
            $form->addFieldError('message', 'Please enter some message text');
        }

        // validate any files
        $attach->isValid();

        if ($this->form->hasErrors()) {
            return;
        }
//        if ($attach->hasFile()) {
//            $attach->moveFile($this->getConfig()->getDataPath() . '/contact/' . date('d-m-Y') . '-' . str_replace('@', '_', $values['email']));
//        }

        if ($this->sendEmail($form)) {
            \Tk\Alert::addSuccess('<strong>Success!</strong> Your form has been sent.');
        } else {
            \Tk\Alert::addError('<strong>Error!</strong> Something went wrong and your message has not been sent.');
        }

        \Tk\Uri::create()->redirect();
    }


    /**
     * @param Form $form
     * @return bool
     * @throws \Exception
     */
    private function sendEmail($form)
    {
        $name = $form->getFieldValue('name');
        $email = $form->getFieldValue('email');
        $type = '';
        if (is_array($form->getFieldValue('type')))
            $type = implode(', ', $form->getFieldValue('type'));
        $messageStr = $form->getFieldValue('message');

        $attachCount = '';
        /** @var Field\File $field */
        $field = $form->getField('attach');
        if ($field->hasFile()) {
            vd($field->getUploadedFiles());
            $attachCount = '<br/><b>Attachments:</b> ';
            foreach ($field->getUploadedFiles() as $file) {
                $attachCount = $file->getClientOriginalName() . ', ';
            }
            $attachCount = rtrim($attachCount, ', ');
        }

        $content = <<<MSG
<p>
Dear $name,
</p>
<p>
Email: $email<br/>
Type: $type
</p>
<p>Message:<br/>
  $messageStr
</p>
<p>
$attachCount
</p>
MSG;

        $message = $this->getConfig()->createMessage();
        $message->addTo($email);
        $message->setSubject($this->getConfig()->get('site.title') . ':  Contact Form Submission - ' . $name);
        $message->set('content', $content);
        if ($field->hasFile()) {
            foreach ($field->getUploadedFiles() as $file) {
                $message->addAttachment($file->getPathname(), $field->getUploadedFile()->getClientOriginalName());
            }
        }
        return $this->getConfig()->getEmailGateway()->send($message);
    }

    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<section>

    <div class="">
      <!-- Contact Form -->
      <h3>Send Us a Message</h3>

      <div class="alert alert-success" role="alert" choice="sent">
        <strong>Success!</strong> Your form has been successfully sent.
      </div>

      <div class="contact-form-wrapper">
        <form id="contactForm" method="post" class="form-horizontal" role="form">

          <div class="form-group">
            <label for="name" class="col-sm-3 control-label">
              <b>Name *</b>
            </label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="name" id="name" placeholder=""/>
            </div>
          </div>

          <div class="form-group">
            <label for="fid-email" class="col-sm-3 control-label">
              <b>Email *</b>
            </label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="email" id="fid-email" placeholder=""/>
            </div>
          </div>

          <div class="form-group" var="group-type">
            <label for="fid-type" class="col-sm-3 control-label">
              <b>Topic</b>
            </label>
            <div class="col-sm-9">
              <select class="form-control" name="type[]" id="fid-type" multiple="true">
                <option value="">Please select topic...</option>
                <option value="General">General</option>
                <option value="Services">Services</option>
                <option value="Orders">Orders</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="fid-attach" class="col-sm-3 control-label">
              <b>Attach</b>
            </label>
            <div class="col-sm-9">
              <input type="file" name="attach[]" id="fid-attach" multiple="true" />
            </div>
          </div>

          <div class="form-group" var="group-message">
            <label for="fid-message" class="col-sm-3 control-label">
              <b>Message *</b>
            </label>
            <div class="col-sm-9">
              <textarea class="form-control" rows="5" name="message" id="fid-message"></textarea>
            </div>
          </div>

          <div class="form-group">
            <div class="col-sm-12">
              <button type="submit" class="btn pull-right btn-success" name="send">Send</button>
            </div>
          </div>

        </form>
      </div>
      <!-- End Contact Info -->
    </div>
</section>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}
