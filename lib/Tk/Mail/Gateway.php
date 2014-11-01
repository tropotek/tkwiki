<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An email gateway object.
 *
 * @package Tk
 */
class Tk_Mail_Gateway extends Tk_Object
{
    static $reffererCheck = true;
    
    /**
     * @var Tk_Mail_Gateway
     */
    static $instance = null;
    
    /**
     *
     */
    private function __construct()
    {
    }
    
    /**
     * Get an instance of the email gateway
     *
     * @return Tk_Mail_Gateway
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Send an email message
     *
     * @param Tk_Mail_Message $message
     */
    static function send(Tk_Mail_Message $message)
    {
        return self::getInstance()->sendMessage($message);
    }
    
    /**
     * Send a mime email message
     *
     * @param Tk_Mail_Message $message
     * @throws Tk_ExceptionRuntime
     */
    private function sendMessage(Tk_Mail_Message $message)
    {
        
        if ((Tk_Config::isDebugMode() && Tk_Config::getDebugEmail() == null)) {
            vd('Email Not Sent: No debug email found in config.');
            return false;
        }
        if ($message->getAddressList() == null || count($message->getAddressList()) == 0) {
            throw new Tk_ExceptionNullPointer("No valid address objects found in message.");
        }
        
        $mail = new htmlMimeMail5();
        
        self::validateField($message->getSubject());
        $mail->setSubject($message->getSubject());
        
        if ($message->isHtml()) {
            $regs = array();
            $mail->setHTML($message->getBody(), $message->getImagesDir());
            preg_match('/<body>(.+)<\/body>/i', $message->getBody(), $regs);
            if (isset($regs[1]) && $regs[1] != null) {
                $mail->setText(strip_tags($regs[1]));
            } else {
                $mail->setText(strip_tags($message->getBody()));
            }
            
            $images = $message->getInlineImages();
            foreach ($images as $file => $mime) {
                $mail->addEmbeddedImage(new fileEmbeddedImage($file, $mime, new Base64Encoding()));
            }
        } else {
            $mail->setText($message->getBody());
        }
        
        $attachments = $message->getAttachments();
        foreach ($attachments as $file => $mime) {
            $mail->addAttachment(new fileAttachment($file, $mime, new Base64Encoding()));
        }
        
        $attachments = $message->getAttachmentObjs();
        foreach ($attachments as $obj) {
            $mail->addAttachment($obj);
        }
        
        if (isset($_SERVER['HTTP_HOST']) && self::$reffererCheck) { // if not a cli script
            $mail->setHeader('X-Sender-IP', getenv('REMOTE_ADDR'));
            $mail->setHeader('X-Referer', getenv('HTTP_REFERER'));
            self::checkReferer(array($_SERVER['HTTP_HOST']));
        }
        
        $success = true;
        if (Tk_Config::isDebugMode()) {
            $mail->setSubject('Debug: ' . $message->getSubject());
            foreach ($message->getAddressList() as $address) {
                $mail->setHeader('X-TkDebug-To', $address->getTo());
                if ($address->getFrom() != null) {
                    $mail->setHeader('X-TkDebug-From', $address->getFrom());
                }
                $bcc = explode(',', $address->getBcc());
                foreach ($bcc as $i => $e) {
                    if (trim($e) == null) {
                        continue;
                    }
                    $mail->setHeader('X-TkDebug-Bcc-' . $i, $e);
                }
                $cc = explode(',', $address->getCc());
                foreach ($cc as $i => $e) {
                    if (trim($e) == null) {
                        continue;
                    }
                    $mail->setHeader('X-TkDebug-Cc-' . $i, $e);
                }
                $b = $mail->send(array(Tk_Config::getDebugEmail()));
                $success = ($b && $success);
            }
        } else {
            foreach ($message->getAddressList() as $address) {
                if ($address->getFrom() != null) {
                    $mail->setFrom($address->getFrom());
                }
                if ($address->getBcc() != null) {
                    $mail->setBcc($address->getBcc());
                }
                if ($address->getCc() != null) {
                    $mail->setCc($address->getCc());
                }
                $b = $mail->send(explode(',', $address->getTo()));
                $success = ($b && $success);
            }
        }
        $mail = null;
        return $success;
    }
    
    /**
     * check_referer() breaks up the enviromental variable
     * HTTP_REFERER by "/" and then checks to see if the second
     * member of the array (from the explode) matches any of the
     * domains listed in the $referers array (declaired at top)
     *
     * @param array $referers
     */
    private function checkReferer($referers)
    {
        
        if (count($referers) > 0) {
            if ($_SERVER['HTTP_REFERER']) {
                $temp = explode('/', $_SERVER['HTTP_REFERER']);
                $found = false;
                while (list(, $stored_referer) = each($referers)) {
                    if (preg_match('/^' . $stored_referer . '$/i', $temp[2]))
                        $found = true;
                }
                if (!$found) {
                    throw new Tk_ExceptionRuntime("You are coming from an unauthorized domain. Illegal Referer.");
                }
            } else {
                throw new Tk_ExceptionRuntime("Sorry, but I cannot figure out who sent you here. Your browser is not sending an HTTP_REFERER.  This could be caused by a firewall or browser that removes the HTTP_REFERER from each HTTP request you submit.");
            }
        } else {
            throw new Tk_ExceptionRuntime("There are no referers defined. All submissions will be denied.");
        }
    }
    
    /**
     * See if a string contains any supicious coding.
     *
     * @param string $str
     * @return string
     */
    static function validateField($str)
    {
        $badStrings = array("content-type:", "mime-version:", "multipart\/mixed", "content-transfer-encoding:", "bcc:", "cc:", "to:");
        
        foreach ($badStrings as $badString) {
            if (preg_match('/'.$badString.'/i', strtolower($str))) {
                throw new Tk_ExceptionRuntime("'$badString' found. Suspected injection attempt - mail not being sent.");
            }
        }
        
        if (preg_match("/(%0A|%0D|\\n+|\\r+)/i", $str) != 0) {
            throw new Tk_ExceptionRuntime("newline found in '$str'. Suspected injection attempt - mail not being sent.");
        }
        return $str;
    }
}