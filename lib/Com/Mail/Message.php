<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A Dom messaging object. Use this to send xml based templates.
 *
 * @package Com
 * @deprecated  Use Tk_Mail_DomMessage
 */
class Com_Mail_Message extends Tk_Mail_Message implements Dom_RendererInterface
{
    /**
     * @var Dom_Template
     */
    protected $template = null;
    
    /**
     * __construct
     *
     * @param Tk_Mail_Address $address
     * @param string $tplFile Path to the message template file (Default: {SitePath}/mail/message.html)
     */
    function __construct($address = null)
    {
        parent::__construct($address);
    }
    
    function __destruct()
    {
        parent::__destruct();
        $this->template = null;
    }
    
    /**
     * Create a message with a template
     *
     * @param Tk_Mail_Address $address
     * @param string $tplFile
     */
    static function create($address = null, $tplFile = '')
    {
        $msg = new self($address);
        if ($tplFile && is_file($tplFile)) {
            $msg->template = Dom_Template::load($tplFile);
        }
        return $msg;
    }
    
    
    /**
     * Set the message html content
     *
     * @param string $html
     */
    function setBody($html)
    {
        if ($this->getTemplate()) {
            $this->getTemplate()->replaceHTML('content', $html);
        } else {
            $this->body = $html;
        }
    }
    
    /**
     * Returns the message body.
     *
     * @return string
     */
    function getBody()
    {
        if ($this->body == null) {
            $this->show();
            $doc = $this->getTemplate()->getDocument();
            if ($this->isHtml()) {
                $this->body = $doc->saveXML();
            } else {
                $this->body = $doc->documentElement->textContent;
            }
        }
        return $this->body;
    }
    
    /**
     * A null show function for the renderer interface
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $template->insertText('requestUri', Tk_Request::requestUri()->toString());
        $template->setAttr('requestUri', 'href', Tk_Request::requestUri()->toString());
        $template->insertText('remoteIp', Tk_Request::remoteAddr());
        $template->insertText('userAgent', Tk_Request::agent());
        $template->insertText('subject', $this->getSubject());
    }
    
    /**
     * getTemplate
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
     * Set the template
     *
     * @param Dom_Template $template
     */
    function setTemplate(Dom_Template $template)
    {
        $this->template = $template;
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
    
    /**
     * Make the template
     *
     * @return Dom_Template
     */
    function __makeTemplate()
    {
        $tplFile = Com_Config::getTemplatePath() . '/mail/Message.html';
        if (is_file($tplFile)) {
            return Dom_Template::load($tplFile);
        }
        
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
p {
  font-size: 90%;
  margin: 2px 0px;
  padding: 0px;
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
}
