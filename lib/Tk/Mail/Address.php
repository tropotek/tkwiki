<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The address base class, used for electronic messaging
 *
 * @package Tk
 */
class Tk_Mail_Address extends Tk_Object
{
    /**
     * @var string
     */
    protected $to = '';
    /**
     * @var string
     */
    protected $from = '';
    /**
     * @var string
     */
    protected $bcc = '';
    /**
     * @var string
     */
    protected $cc = '';
    
    /**
     * __construct
     *
     * @param string $email
     */
    function __construct($email)
    {
        $this->setTo($email);
    }
    
    /**
     * Create an address object
     *
     * @param string $to
     * @param string $from
     * @return Tk_Mail_Address
     */
    static function create($to, $from = '')
    {
        $obj = new self($to);
        if ($from) {
            $obj->setFrom($from);
        }
        return $obj;
    }
    
    
    /**
     * Returns the to arress array.
     *
     * @return string
     */
    function getTo()
    {
        return $this->to;
    }
    
    /**
     * Sets the recipient address.
     *
     * @param string
     * @return Tk_Mail_Address
     */
    function setTo($email)
    {
        if (!preg_match(Tk_Util_Validator::REG_EMAIL, $email)) {
            //throw new Tk_ExceptionIllegalArgument('Invalid email value: `' . $email . '`');
            Tk::log('Invalid email value: `' . $email . '`');
        }
        Tk_Mail_Gateway::validateField($email);
        $this->to = $email;
        return $this;
    }
    
    /**
     * Returns the from address.
     *
     * @return string
     */
    function getFrom()
    {
        return $this->from;
    }
    
    /**
     * Sets the from address.
     *
     * @param string $email
     * @return Tk_Mail_Address
     */
    function setFrom($email)
    {
        Tk_Mail_Gateway::validateField($email);
        $this->from = $email;
        return $this;
    }
    
    /**
     * Returns the Bcc address.
     *
     * @return string
     */
    function getBcc()
    {
        return $this->bcc;
    }
    
    /**
     * Sets the Bcc address.
     *
     * @param string $email
     * @return Tk_Mail_Address
     */
    function setBcc($email)
    {
        Tk_Mail_Gateway::validateField($email);
        $this->bcc = $email;
        return $this;
    }
    
    /**
     * Returns the Cc address.
     *
     * @return string
     */
    function getCc()
    {
        return $this->cc;
    }
    
    /**
     * Sets the Cc address.
     *
     * @param string $email
     * @return Tk_Mail_Address
     */
    function setCc($email)
    {
        Tk_Mail_Gateway::validateField($email);
        $this->cc = $email;
        return $this;
    }
}