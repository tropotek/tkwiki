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
class Adm_Modules_Support extends Com_Web_Component
{

    /**
     * init
     *
     */
    function init()
    {

        $form = Form::create('Support');
        $redirect = Tk_Request::requestUri()->delete('send');
        $form->addEvent(Adm_Event_Send::create('send', $redirect));

        // TODO: Fix once Com_Auth is deleted
        $user = null;
        if (Tk::moduleExists('Auth')) {
            $user = Auth::getUser();
        } else {
            $user = $this->getUser();
        }


        $form->addField(Form_Field_Hidden::create('username'))->setValue($user->getUsername());
        $form->addField(Form_Field_Hidden::create('userId'))->setValue($user->getId());

        $form->addField(Form_Field_Text::create('name'))->setRequired(true)->setValue($user->getUsername());
        $form->addField(Form_Field_Text::create('subject'))->setRequired(true);
        $form->addField(Form_Field_Textarea::create('comments'))->setLabel('Description')->setRequired(true)->setWidth(300)->setHeight(80);

        $this->setForm($form);


    }

    /**
     * Show
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        if (Tk_Session::getOnce('SupportForm-sent') == 't') {
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
class Adm_Support_Validator extends Tk_Util_Validator
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
        if (!preg_match('/^.{1,255}$/', $this->obj->getFieldValue('subject'))) {
            $this->setError('subject', 'Invalid Subject Text.');
        }
        if (strlen($this->obj->getFieldValue('comments')) <= 0) {
            $this->setError('comments', 'Please tell us your support requirements');
        }

    }
}



class Adm_Event_Send extends Form_ButtonEvent
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
        $valid = new Adm_Support_Validator($this->getForm());
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
        $to = Com_Config::getSupportEmail();
        $address = Tk_Mail_Address::create($to, $this->getForm()->getFieldValue('email'));

        $message = Tk_Mail_DomMessage::create($address, Tk_Config::get('system.templatePath').'/mail/message.html');
        $message->setSubject($_SERVER['HTTP_HOST'] . ' - Support enquiry ');

        $html = sprintf('
<table border="0">
  <tr>
    <td>Name:</td>
    <td>%s</td>
  </tr>
  <tr>
    <td>Subject:</td>
    <td>%s</td>
  </tr>
  <tr>
    <td>Message:</td>
    <td>%s</td>
  </tr>

</table>
        ', strip_tags($this->getForm()->getFieldValue('username')), $this->getForm()->getFieldValue('subject'), nl2br(strip_tags($this->getForm()->getFieldValue('comments'))));
        $message->setContent($html);

        if ($message->send()) {
            Tk_Session::set('SupportForm-sent', 't');
        }

    }

}