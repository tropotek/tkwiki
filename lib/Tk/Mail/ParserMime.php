<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

require_once dirname(__FILE__) . '/../Other/mimeMailPaser/mime_parser.php';
require_once dirname(__FILE__) . '/../Other/mimeMailPaser/rfc822_addresses.php';

/**
 * A mail message decoder class
 *
 * @package Tk
 * @see mime_parser_class
 * @see http://www.phpclasses.org/browse/package/3169.html
 */
class Tk_Mail_ParserMime extends Tk_Object
{
    /**
     * @var Tk_Mail_MimeDecode
     */
    static $instance = null;
    
    /**
     * @var mime_parser_class
     */
    private $parser = null;
    
    /**
     * @var boolean
     */
    protected $parseMulti = false;
    
    /**
     * @var boolean
     */
    protected $ignoreErrors = true;
    
    /**
     * __construct
     */
    private function __construct()
    {
    }
    
    /**
     * Get an instance of the email gateway
     *
     * @return Tk_Mail_MimeDecode
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Parse a mail message from a string and return the parts of the message
     *
     * @param string $msgStr The message source
     * @return array An array containing the messages.
     */
    static function parseString($msgStr)
    {
        $message = array();
        $params = array('Data' => $msgStr, 'SkipBody' => 0);
        if (!self::getInstance()->getParser()->Decode($params, $message)) {
            throw new RuntimeException('Failed to decode the message.');
        }
        return $message;
    }
    
    /**
     * Parse a mail message from a file and return the parts of the message
     * To parse multiple messages from a file call setParseMultiple(true) first.
     *
     * @param string $msgFile The message source
     * @return array
     */
    static function parseFile($msgFile)
    {
        if (!is_file($msgFile)) {
            throw new RuntimeException('Cannot read message file.');
        }
        $message = array();
        $params = array('File' => $msgFile, 'SkipBody' => 0);
        self::getInstance()->getParser()->Decode($params, $message);
        return $message;
    }
    
    /**
     * Get the paser error messages
     *
     * @return string
     */
    static function getError()
    {
        return self::getInstance()->getParser()->error;
    }
    
    /**
     * Get any parser warnings
     *
     * @return array
     */
    static function getWarnings()
    {
        return self::getInstance()->getParser()->warnings;
    }
    
    /**
     * Analise a parsed message array
     *
     * @param array $msgArray
     * @return array
     */
    static function analise($msgArray)
    {
        $results = array();
        foreach ($msgArray as $msg) {
            $result = array();
            if (!self::getInstance()->getParser()->Analize($msg, $result)) {
                throw new RuntimeException('Failed to analise the message: ' . self::getError());
            }
            $results[] = $result;
        }
        return $results;
    }
    
    /**
     * Set the paser to parse multiple messages
     *
     * @param boolean $b
     */
    static function setParseMultiple($b)
    {
        self::getInstance()->parseMulti = $b === true ? true : false;
    }
    
    /**
     * Set the paser to parse multiple messages
     *
     * @param boolean $b
     */
    static function setIgnoreErrors($b)
    {
        self::getInstance()->ignoreErrors = $b === true ? true : false;
    }
    
    /**
     * Get and config the mime object
     *
     * @return mime_parser_class
     */
    protected function getParser()
    {
        if ($this->parser == null) {
            $this->parser = new mime_parser_class();
        }
        $this->parser->mbox = $this->parseMulti ? 1 : 0;
        $this->parser->decode_bodies = 1;
        $this->parser->ignore_syntax_errors = $this->ignoreErrors ? 1 : 0;
        
        return $this->parser;
    }
    
    /**
     * flatMimeDecode
     * Use this to decode subject text or other encoded header text.
     *
     * @param string $string
     * @return string
     */
    static function flatMimeDecode($string)
    {
        $array = imap_mime_header_decode($string);
        $str = "";
        foreach ($array as $part) {
            $str .= $part->text;
        }
        return $str;
    }

}