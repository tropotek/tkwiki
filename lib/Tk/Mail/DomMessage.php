<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Tk_Mail_DomMessage
 *
 * @package Tk
 */
class Tk_Mail_DomMessage extends Tk_Mail_Message implements Dom_RendererInterface
{
    /**
     * @var Dom_Template
     */
    private $template = null;
    
    /**
     * @var string
     */
    protected $content = '';
    
    
    
    /**
     * Create a message with a template
     *
     * @param Tk_Mail_Address $address
     * @param string $tplFile
     * @return Tk_Mail_DomMessage
     */
    static function create($address = null, $tplFile = '')
    {
        $msg = new self($address);
        if ($tplFile && is_file($tplFile)) {
            $msg->setTemplate(Dom_Template::load($tplFile));
        }
        $msg->setIsHtml(true);
        return $msg;
    }
    
    /**
     * Send this message to its recipients.
     *
     * @param Tk_Mail_Address $address Note: this will clear any previously added addresses
     * @return boolean True if all emails are sent successfuly false if there are any errors
     */
    function send($address = null)
    {
        if (!$this->getBody()) {
            $this->show();
            $this->setBody($this->getTemplate()->toString());
        }
        return parent::send($address);
    }
    
    /**
     * Set the message html content
     * 
     * @param string $html
     */
    function setContent($html)
    {
        $this->content = $html;
    }
    
    /**
     * Execute the renderer.
     *
     */
    function show()
    {
        $template = $this->getTemplate();
        $template->insertHtml('content', $this->content);
        $template->insertText('subject', $this->getSubject());
        
        $template->insertText('requestUri', Tk_Request::requestUri()->toString());
        $template->setAttr('requestUri', 'href', Tk_Request::requestUri()->toString());
        $template->insertText('remoteIp', Tk_Request::remoteAddr());
        $template->insertText('userAgent', Tk_Request::agent());
    }
    

    /**
     * Make the template
     *
     * @return Dom_Template
     */
    function __makeTemplate()
    {
        $xmlStr = '
<html>
<head>
  <title>Email</title>
  
  <style type="text/css">
body {
  font-family: arial,sans-serif;
  font-size: 80%;
  padding: 5px;
  background-color: #FFF;
}
  </style>
</head>
<body>
  
  <h2 var="subject"></h2>
  <hr/>
  <p>&#160;</p>
  <div class="content" var="content"></div>
  <p>&#160;</p>
  <div class="footer">
      <hr />
      <p>
        <i>Page:</i> <a href="#" var="requestUri"></a><br/>
        <i>IP Address:</i> <span var="remoteIp"></span><br/>
        <i>User Agent:</i> <span var="userAgent"></span>
      </p>
  </div>
</body>
</html>';
        
        return Dom_Template::load($xmlStr);
    }
    
    
    /**
     * Set a new template for this renderer.
     *
     * @param Dom_Template $template
     */
    function setTemplate(Dom_Template $template)
    {
        $this->template = $template;
    }
    
    /**
     * Get the template
     * This method will try to call the magic method __makeTemplate
     * to get a template if non exsits.
     * Use this for object that use internal templates.
     *
     * @return Dom_Template
     */
    function getTemplate()
    {
        $magic = '__makeTemplate';
        if (!$this->hasTemplate() && method_exists($this, $magic)) {
            $this->template = $this->$magic();
        }
        return $this->template;
    }
    
    /**
     * Test if this renderer has a template and is not NULL
     *
     * @return boolean
     */
    function hasTemplate()
    {
        if ($this->template) {
            return true;
        }
        return false;
    }

}