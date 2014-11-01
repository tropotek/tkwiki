<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An admin content box. Put text and stats within these box's on the admin home page
 *
 * @package Com
 */
class Ext_Modules_Form_Contact extends Com_Web_Component
{

    /**
     * init
     *
     */
    function init()
    {

        $form = Form::create('Contact');
        $redirect = Tk_Request::requestUri()->delete('send');
        $form->addEvent(Form_Event_Send::create('send', $redirect));

        $form->addField(Form_Field_Text::create('name'))->setRequired(true);
        $form->addField(Form_Field_Text::create('email'))->setRequired(true);
        $form->addField(Form_Field_Textarea::create('message'))->setLabel('Message')->setRequired(true)->setWidth(300)->setHeight(100);
        $form->addField(Form_Field_Captcha::create('validate'))->setNotes('Please copy the letters you see in the image for verification.');

        $this->setForm($form);
    }

    /**
     * Show
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        if (Tk_Session::getOnce('ContactForm-sent') == 't') {
            $template->setChoice('thanks');
        } else {
            $template->setChoice('Form');
        }


    }

}

/**
 *  Validate a FormMail submission
 *
 * @package Ui
 */
class Ext_Contact_Validator extends Tk_Util_Validator
{
    /**
     * @var Form
     */
    protected $obj = null;

    /**
     * Validates an email address
     */
    function validate()
    {
        if (!preg_match('/^.{1,255}$/i', $this->obj->getFieldValue('name'))) {
            $this->setError('name', 'Invalid Name.');
        }
        if (!preg_match(self::REG_EMAIL, $this->obj->getFieldValue('email'))) {
            $this->setError('email', 'Invalid Email Address.');
        }
        if (strlen($this->obj->getFieldValue('message')) <= 0) {
            $this->setError('message', 'Please tell us your message.');
        }

    }
}



class Form_Event_Send extends Form_ButtonEvent
{


    /**
     * Create an instance of this object
     *
     * @param string $name
     * @return Form_Event_Send
     */
    static function create($name)
    {
        $o = new self($name);
        return $o;
    }


    function execute()
    {
        $valid = new Ext_Contact_Validator($this->getForm());
        $this->getForm()->addFieldErrors($valid->getErrors());
        if ($this->getForm()->hasErrors()) {
            return false;
        }
        $this->sendEmail();

        $this->setRedirect(Tk_Request::requestUri()->delete('send')->redirect());
    }

    /**
     * sendEmail
     *
     * @param array $arr
     */
    function sendEmail()
    {
        $to = Wik_Db_Settings::getInstance()->getSiteEmail();
        $from = $this->getForm()->getFieldValue('email');
        $html = '';

        $fields = $this->form->getFieldList();
        /* @var $field Form_Field */
        foreach ($fields as $field) {
            if ($field instanceof Form_Field_Hidden || $field instanceof Form_Field_Captcha) {
                continue;
            }
            $html .= sprintf('
  <tr>
    <th>%s:</th>
    <td>%s</td>
  </tr>', $field->getLabel(), $field->getValue());
        }

        if ($html) {
            $html = '<table border="0">' . $html . '</table>';
        }


        $address = Tk_Mail_Address::create($to, $from);
        $message = new Tk_Mail_Message($address);
        $message->setSubject($_SERVER['HTTP_HOST'] . ' - Form enquiry ');
        $message->setBody($message->createHtmlTemplate($html));
        if ($message->send()) {
            Tk_Session::set('ContactForm-sent', 't');
        } else {
            return;
        }

        $address = Tk_Mail_Address::create($from, $to);
        $message = new Tk_Mail_Message($address);
        $message->setSubject($_SERVER['HTTP_HOST'] . ' - Form enquiry ');
        $html = sprintf('
<p>To %s</p>
<p>
  Your message has been sent to %s and we will reply as soon as possible.
</p>
Thank You,
</p>
        ', strip_tags($this->getForm()->getFieldValue('name')), $from );
        $message->setBody($message->createHtmlTemplate($html));
        $message->send();

    }


}