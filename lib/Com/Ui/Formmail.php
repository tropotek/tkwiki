<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A highly configurable formail component. Great for quick mail forms like contact.
 *
 *
 * <code>
 *
 *   <form id="__formmail" method="post">
 *     <input type="hidden" name="__subject" value="Website Contact Us Form Submission." />
 *     <input type="hidden" name="__recipient" value="email@example.com" />
 *     <input type="hidden" name="__replyTo" value="email@example.com" />
 *     <input type="hidden" name="__template" value="/html/mail/message.html" />
 *     <input type="hidden" name="__redirect" value="http://www.example.com/thanks.html" />
 *
 *     <div class="required">
 *       <p class="error" choice="name-error" var="name-error"></p>
 *       <label for="fid-name">Name:</label>
 *       <input type="text" name="name" id="fid-name" class="inputText" maxlength="100" reg=".+" />
 *     </div>
 *     <div class="required">
 *       <p class="error" choice="email-error" var="email-error"></p>
 *       <label for="fid-email">Email:</label>
 *       <input type="text" name="email" id="fid-email" class="inputText" maxlength="100" reg="^[0-9a-zA-Z]([-_.]*[0-9a-zA-Z])*@[0-9a-zA-Z]([-.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,6}$" />
 *     </div>
 *     <div class="optional">
 *       <p class="error" choice="address1-error" var="address1-error"></p>
 *       <label for="fid-address1">Address:</label>
 *       <input type="text" name="address1" id="fid-address1" class="inputText" maxlength="100" />
 *     </div>
 *     <div class="optional">
 *       <p class="error" choice="address2-error" var="address2-error"></p>
 *       <label for="fid-address2">&#160;</label>
 *       <input type="text" name="address2" id="fid-address2" class="inputText" maxlength="100"/>
 *     </div>
 *     <div class="required">
 *       <p class="error" choice="notes-error" var="notes-error"></p>
 *       <label for="fid-notes">Your Message:</label>
 *       <textarea name="notes" id="fid-notes" class="inputTextarea" rows="10" cols="60"></textarea>
 *       <small>Must be 250 characters or less.</small>
 *     </div>
 *
 *     <input type="submit" name="send" value="Submit" class="inputSubmit"/>
 *     <input type="reset" name="reset" value="Reset" class="inputSubmit"/>
 *
 *   </form>
 *
 * </code>
 *
 *
 * This is an example of an Sdk formmail form.
 *
 *  o (required) __subject: If omitted default subject text will be used<br>
 *  o (required) __recipient: If omitted an exception will be thrown<br>
 *  o (optional) __replyTo: If omitted the it will not be set in the headers<br>
 *  o (optional) __template: If omitted an internal HTML template will be used<br>
 *  o (optional) __redirect: If omitted an internal thank you message will be displayed, use a full URL path not relative.<br>
 *
 * All hidden fields containing the above names will be hidden from public view upon rendering of the form
 * to ensure no sensative details (EG: mail/filesystem) will be published to the page.
 *
 * Each form element can contain an optional `reg` attribute whose value is a regular expression
 * to validate the field on submission if it fails then the for will return with an error in the `{filedname}-error` var/choice
 *
 * @package Com
 */
class Com_Ui_Formmail extends Com_Web_Component
{
    /**
     * @var string
     */
    protected $formId = '';

    /**
     * @var Com_Form_Object
     */
    protected $form = null;

    /**
     * @var Dom_Form
     */
    protected $domForm = null;

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $recipient = '';

    /**
     * @var string
     */
    protected $replyTo = '';

    /**
     * @var string
     */
    protected $emailTemplate = '';

    /**
     * @var string
     */
    protected $redirect = '';




    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->addEvent($this->getEventKey('send'), 'doSubmit');

        $this->subject = 'Contact email from site ' . $_SERVER['HTTP_HOST'];
    }

    /**
     * The default init method
     *
     */
    function init()
    {
        $comElement = $this->getTemplate()->getVarElement($this->getInsertVar());
        if ($comElement->hasAttribute('id')) {
            $this->formId = $comElement->getAttribute('id');
        } else {
            $this->formId = $comElement->getAttribute('name');
        }

        $this->domForm = $this->getTemplate()->getForm($this->formId);
        $el = $this->domForm->getFormElement('send');
        $el->setAttribute('name', $this->getEventKey('send'));
        $this->form = new Com_Form_Object($this->formId);
        $elements = $this->domForm->getElementNames();
        foreach ($elements as $name) {
            if ($name == 'submit' || $name == 'send' || $name == 'reset') {
                continue;
            }
            if (substr($name, 0, 2) == '__') {
                $el = $this->domForm->getFormElement($name)->getNode();
                $param = substr($name, 2);
                $this->$param = $el->getAttribute('value');
                $el->parentNode->removeChild($el);
            } else {
                $this->form->addField($name);
            }
        }
        if ($this->recipient == null) {
            throw new Tk_ExceptionNullPointer('No valid recipient found. Add the form element <input type="hidden" name="__recipient" value="some@email.com" />');
        }

    }

    /**
     * The default event handler.
     *
     */
    function doDefault()
    {
    }

    function doSubmit()
    {
        $this->form->loadFromRequest($this->getRequest());
        $valid = new Com_Ui_FormmailValidator($this->domForm);

        if ($this->form->getField('valid')) {
            $session = Tk_Session::getInstance();
            $hashArr = $session->getParameter('Dk_formImage');
            $sesHash = $hashArr['_f100'];
            $usrHash = md5($this->form->getFieldValue('valid')) . 'f33f';
            if ($usrHash != $sesHash) {
                $this->form->addFieldError('valid', 'Invalid verification code, try again.');
            }
        }

        $this->form->addFieldErrors($valid->getErrors());
        if ($this->form->hasErrors()) {
            return;
        }

        $address = new Tk_Mail_Address($this->recipient);
        if ($this->form->getFieldValue('email') != null) { //TODO: Use email reg here
            $address->setFrom($this->form->getFieldValue('email'));
        } else if ($this->replyTo) {
            $address->setFrom($this->replyTo);
        }
        $message = new Com_Mail_Message($address);
        $message->setSubject($this->subject);
        if ($this->emailTemplate) {
            $emailTemplate = Dom_Template::load($this->getConfig()->getSitePath() . $this->emailTemplate);
            $message->setTemplate($emailTemplate);
        }

        // Render Email
        $contentHtml = '<table border="0" cellpadding="0" cellspacing="0" class="list">
      <tr>
        <th class="field">Field</th>
        <th class="data">Value</th>
      </tr>';
        /* @var $field Com_Form_Field */
        foreach ($this->form->getFields() as $field) {
            $name = ucfirst($field->getName());
            $value = htmlentities($field->getValue());
            $contentHtml .= "<tr><td class=\"field\">$name: </td><td class=\"data\">$value</td></tr>\n";
        }
        $contentHtml .= '</table>';

        $message->setBody($contentHtml);
        $message->send();

        Tk_Session::set('formmail.success', true);

        $url = $this->getRequest()->getRequestUri();
        $url->delete($this->getEventKey('send'));
        $url->redirect();
    }

    /**
     * The default show method.
     *
     */
    function show()
    {
        $template = $this->getTemplate();
        
        if (Tk_Session::getOnce('formmail.success')) {
            $template->setChoice('success');
        }

        $renderer = new Com_Form_Renderer($this->form);
        $renderer->show($template);

        if ($this->form->getField('valid')) {
            $validImg = new Tk_Type_Url('/lib/Com/Form/validImg.php');
            $validImg->set('sid', $this->getSession()->getId());
            $validImg->set('idx', '_f100');
            $template->setAttr('valid', 'src', $validImg->toString());
        }
    }

}

/**
 *  Validate a FormMail submission
 *
 * @package Com
 */
class Com_Ui_FormmailValidator extends Tk_Util_Validator
{
    /**
     * @var Dom_Form
     */
    protected $obj = null;

    /**
     * Validates a form based on the form nodes regular expression
     *
     * @param Dom_Form $domForm
     */
    function __construct(Dom_Form $domForm)
    {
        parent::__construct($domForm);
    }

    /**
     * Validates an email address
     */
    function validate()
    {
        $fields = $this->obj->getElementNames();
        foreach ($fields as $fieldName) {
            if ($fieldName == 'valid') {
                continue;
            }
            $field = $this->obj->getFormElement($fieldName);
            $node = $field->getNode();
            if ($node->hasAttribute('reg')) {
                $reg = $field->getAttribute('reg');
                $node->removeAttribute('reg');
                if ($reg != null && !preg_match('/'.$reg.'/', Tk_Request::getInstance()->getParameter($fieldName))) {
                    $this->setError($fieldName, "Invalid $fieldName value");
                }
            }
        }
    }
}