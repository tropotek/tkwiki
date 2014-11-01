<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Tk_Mail_Message
 *
 *
 *
 * @package Tk
 */
class Tk_Mail_Message extends Tk_Object
{
    /**
     * @var array
     */
    protected $addressList = array();
    
    /**
     * @var string
     */
    protected $subject = '{No Subject}';
    
    /**
     * @var string
     */
    protected $body = '';
    
    /**
     * @var boolean
     */
    protected $isHtml = true;
    
    /**
     * @var string
     */
    protected $imagesDir = '';
    
    /**
     * @var array
     */
    protected $images = array();
    
    /**
     * @var array
     */
    protected $attachments = array();
    
    /**
     * @var array
     */
    protected $attachmentObjs = array();
    
    
    
    /**
     * __construct
     *
     * @param Tk_Mail_Address $address
     */
    function __construct($address = null)
    {
        $this->addAddress($address);
    }
    
    function __destruct()
    {
        $this->addressList = null;
        $this->subject = null;
        $this->body = null;
        $this->imagesDir = null;
        $this->images = null;
        $this->attachmentObjs = null;
        $this->attachments = null;
            
    }
    
    /**
     * Send this message to its recipients.
     *
     * @param Tk_Mail_Address $address Note: this will clear any previously added addresses
     * @return boolean True if all emails are sent successfuly false if there are any errors
     */
    function send($address = null)
    {
    	if ($address instanceof Tk_Mail_Address) {
    		$this->addressList = array();
            $this->addAddress($address);
    	}
        return Tk_Mail_Gateway::send($this);
    }
    
    /**
     * Add a recipiant address to the message
     *
     * @param Tk_Mail_Address $address
     */
    function addAddress($address)
    {
        if ($address) {
            $this->addressList[] = $address;
        }
    }
    
    /**
     * Set the array of Tk_Mail_Address objects to use
     * Alternativly you can send a single Tk_Mail_Address object
     *
     * @param array $list
     */
    function setAddressList($list)
    {
        if (!is_array($list)) {
            throw new Tk_ExceptionIllegalArgument('Address list is not of type \'array\'.');
        }
        $this->addressList = $list;
    }
    
    /**
     * Get the message address List
     *
     * @return array
     */
    function getAddressList()
    {
        return $this->addressList;
    }
    
    /**
     * The message text body
     *
     * @param string $body
     * @param boolean $useHtmlTemplate
     */
    function setBody($body)
    {
        $this->body = $body;
    }
    
    /**
     * Returns the message body.
     *
     * @return string
     */
    function getBody()
    {
        return $this->body;
    }
    
    /**
     * Set the subject
     *
     * @param string $subject
     */
    function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    /**
     * Returns the message subject.
     *
     * @return string
     */
    function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * Set to true if this message is a html/mime message
     *
     * @param boolean $b
     */
    function setIsHtml($b)
    {
        $this->isHtml = $b;
    }
    
    /**
     * Is this message a html message
     *
     * @return boolean
     */
    function isHtml()
    {
        return $this->isHtml;
    }
    
    /**
     * Set the source location for attaching images
     *
     * @param string $dir
     */
    function setImagesDir($dir)
    {
        $this->imagesDir = $dir;
    }
    
    /**
     * Get the source location for the images dir
     *
     * @return string
     */
    function getImagesDir()
    {
        return $this->imagesDir;
    }
    
    /**
     * Attatch an inline image file to the message
     *
     * @param string $file
     * @param string $mimeType
     */
    function addInlineImage($file, $mimeType)
    {
        $this->images[$file] = $mimeType;
    }
    
    /**
     * Get the map of inline image paths and mime types
     *
     * @return array
     */
    function getInlineImages()
    {
        return $this->images;
    }
    
    /**
     * Add an attachment to this message
     *
     * @param string $file
     */
    function addAttachment($file, $mimeType)
    {
        $this->attachments[$file] = $mimeType;
    }
    
    /**
     * Get an array of all attached files
     *
     * @return array
     */
    function getAttachments()
    {
        return $this->attachments;
    }
    
    /**
     * Add an attachment obj to this message
     *
     * use the object in the htmlMimeMail5 objects
     *
     * @param attachment $file
     */
    function addAttachmentObjs($attachment)
    {
        $this->attachmentObjs[] = $attachment;
    }
    
    /**
     * Get an array of all attached objects
     *
     * @return array
     */
    function getAttachmentObjs()
    {
        return $this->attachmentObjs;
    }
    
    /**
     * Make a default HTML template to create HTML emails
     * usage:
     *  $message->setBody($message->createHtmlTemplate($bodyStr));
     *
     * @return string
     */
    function createHtmlTemplate($body)
    {
        $request = Tk_Request::getInstance();
        $defaultHtml = sprintf('
<html>
<head>
  <title>Email</title>
  
<style type="text/css">
body {
  font-family: arial,sans-serif;
  font-size: 80%%;
  padding: 5px;
  background-color: #FFF;
}

p {
  margin: 2px 0px;
  padding: 0px;
}

</style>
</head>
<body>
  
  <h2>%s</h2>
  <hr/>
  <p>&#160;</p>
  <div class="content">%s</div>
  <p>&#160;</p>
  <div class="footer">
      <hr />
      <p>
        <i>Page:</i> <a href="%s">%s</a><br/>
        <i>IP Address:</i> <span>%s</span><br/>
        <i>User Agent:</i> <span>%s</span>
      </p>
  </div>
</body>
</html>', $this->getSubject(), $body, $request->getRequestUri()->toString(), $request->getRequestUri()->toString(), $request->getRemoteAddr(), $request->getUserAgent());
        
        return $defaultHtml;
    }
    
    /**
     * Return a string representation of this message
     *
     * @return string
     */
    function toString()
    {
        $str = '';
        $str .= 'inline images: ' . count($this->images) . "\n";
        $str .= 'attatchments: ' . count($this->attachments) . "\n";
        $str .= 'isHtml: ' . $this->isHtml ? 'Yes' : 'No' . "\n";
        $str .= 'imagesDir: ' . $this->imagesDir . "\n";
        $str .= 'subject: ' . $this->getSubject() . "\n";
        $str .= "body:  \n-----\n" . $this->getBody() . "\n-----\n";
        return $str;
    }

}