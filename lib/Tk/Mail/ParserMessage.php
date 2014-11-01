<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This object is instansiated by the ParserMime object.
 * It will contain a decoded email message.
 * See the ParserMime::getInstance() function.
 *
 * @package Tk
 */
class Tk_Mail_ParserMessage extends Tk_Object
{
    
    /**
     * @var string
     */
    private $raw = '';
    
    /**
     * @var array
     */
    private $result = array();
    
    /**
     * @var array
     */
    private $from = array();
    
    /**
     * @var array
     */
    private $replyTo = array();
    
    /**
     * @var array
     */
    private $to = array();
    
    /**
     * @var array
     */
    private $cc = array();
    
    /**
     * @var string
     */
    private $subject = '';
    
    /**
     * @var string
     */
    private $textBody = '';
    
    /**
     * @var string
     */
    private $htmlBody = '';
    
    /**
     * @var array
     */
    private $attachments = array();
    
    /**
     * @var string
     */
    private $messageId = '';
    
    /**
     * @var Tk_Util_Date
     */
    private $date = null;
    
    /**
     * __construct
     *
     * @param string $rawMsg
     * @param boolean $parseMulti
     * @param boolean $ignoreErrors
     */
    function __construct($rawMsg)
    {
        //$this->raw = $rawMsg;
        $this->result = Tk_Mail_ParserMime::parseString($rawMsg);
        $this->result = $this->result[0];
        $this->init();
    }
    
    /**
     * initalise the object
     */
    private function init()
    {
        // DATE
        $this->date = Tk_Util_Date::createDate();
        if (isset($this->result['Headers']['date:'])) {
            $this->date = Tk_Util_Date::createDate(strtotime($this->result['Headers']['date:']));
        }
        
        // Message Id
        if (isset($this->result['Headers']['message-id:'])) {
            $this->messageId = $this->result['Headers']['message-id:'];
        }
        if ($this->messageId == '') {
            $this->messageId = '<' . md5($this->date->getTimestamp()) . '@tropotek.com.au>';
        }
        
        // Subject
        if (isset($this->result['DecodedHeaders']['subject:'][0][0]['Value'])) {
            $this->subject = $this->result['DecodedHeaders']['subject:'][0][0]['Value'];
        } else if (isset($this->result['Headers']['subject:'])) {
            $this->subject = Tk_Mail_ParserMime::flatMimeDecode($this->result['Headers']['subject:']);
        }
        
        // FROM
        if (isset($this->result['ExtractedAddresses']['from:'])) {
            $this->from = $this->result['ExtractedAddresses']['from:'];
        }
        
        // TO
        if (isset($this->result['ExtractedAddresses']['to:'])) {
            $this->to = $this->result['ExtractedAddresses']['to:'];
        }
        
        // replyTo
        if (isset($this->result['ExtractedAddresses']['reply-to:'])) {
            $this->replyTo = $this->result['ExtractedAddresses']['reply-to:'];
        }
        
        // CC
        if (isset($this->result['ExtractedAddresses']['cc:'])) {
            $this->cc = $this->result['ExtractedAddresses']['cc:'];
        }
        
        // Text, HTML, Attachments
        $this->getParts($this->result);
    }
    
    /**
     * Get the message ID
     * If none found a hashmap of the date with '@tropotek.com.au' is used
     *
     * @return string
     */
    function getMessageId()
    {
        return $this->messageId;
    }
    
    /**
     * Get the subject line
     *
     * @return string
     */
    function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * Get the from address array
     * In parts array('address' => '{emailAddres}', 'name' => '{text name}');
     *
     * @return array
     */
    function getFrom()
    {
        return $this->from;
    }
    
    /**
     * Get the to address array
     * In parts array('address' => '{emailAddres}', 'name' => '{text name}');
     *
     * @return array
     */
    function getTo()
    {
        return $this->to;
    }
    
    /**
     * Get the replyTo address array
     * In parts array('address' => '{emailAddres}', 'name' => '{text name}');
     *
     * @return array
     */
    function getReplyTo()
    {
        return $this->replyTo;
    }
    
    /**
     * Get the CC address array
     * In parts array('address' => '{emailAddres}', 'name' => '{text name}');
     *
     * @return array
     */
    function getCC()
    {
        return $this->cc;
    }
    
    /**
     * Get the message Attachments
     *
     * @return array
     */
    function getAttachments()
    {
        return $this->attachments;
    }
    
    /**
     * Get the date the message was sent.
     *
     * @return Tk_Util_Date
     */
    function getDate()
    {
        return $this->date;
    }
    
    function getTextBody()
    {
        return $this->textBody;
    }
    
    function getHtmlBody()
    {
        return $this->htmlBody;
    }
    
    /**
     * Get the raw message string
     *
     * @return string
     */
    function getRawMessage()
    {
        return $this->raw;
    }
    
    /**
     * Get the parsed result array
     *
     * @return array
     */
    function getResult()
    {
        return $this->result;
    }
    
    /**
     * Get any paser error message
     *
     * @return string
     */
    function getError()
    {
        return Tk_Mail_ParserMime::getError();
    }
    
    /**
     * Get any parser warnings
     *
     * @return array
     */
    function getWarnings()
    {
        return Tk_Mail_ParserMime::getWarnings();
    }
    
    /**
     * Get teh body and attachements from the decoded message
     *
     */
    private function getParts($data)
    {
        // BODY
        if (isset($data['Parts']) && count($data['Parts']) == 0) { // not multipart
            $this->htmlBody = $data['Body'];
            $this->textBody = strip_tags($data['Body']);
        } else { // multipart: iterate through each part
            foreach ($data['Parts'] as $part) {
                $this->getPart($part);
            }
        }
    }
    
    /**
     * The recursive function to get the multiple part message..
     */
    private function getPart($part)
    {
        $body = $part['Body'];
        $type = strtolower($part['Headers']['content-type:']);
        $encoding = strtolower($part['Headers']['content-transfer-encoding:']);
        
        if (isset($part['FileName'])) {
            $this->attachments[] = array('Name' => $part['FileName'], 'Body' => $part['Body']);
        } else if (preg_match('/text\/plain/', $type)) {
            $this->textBody .= trim($part['Body']) . "\n\n";
        } else if (preg_match('/text\/html/', $type)) {
            // TODO: Wrap the message in a blockquote and only get the <body></body> html for nested parts
            $this->htmlBody .= $part['Body'] . "\n<p>&#160;</p>\n";
        } elseif (preg_match('/^message/i', $type) && $this->textBody) { // for bounce, nested messages
            $this->textBody .= trim($body) . "\n\n";
        }
        
        // SUBPART RECURSION
        if (count($part['Parts']) > 0) {
            foreach ($part['Parts'] as $p2) {
                $this->getPart($p2);
            }
        }
    }
    

}
