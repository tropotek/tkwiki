<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * This Auth object validates a user and holds
 *   the login and home urls.
 *
 * @package Util
 */
final class Com_Util_BotDetector
{
    /**
     * @var Com_Util_BotDetector
     */
    static protected $instance = null;
    
    /**
     * @var string
     */
    private $botName = '';

    
    /**
     * Sigleton, No instances can be created.
     * Use:
     *   Com_Util_BotDetector::getInstance()
     */
    protected function __construct()
    {
        $this->detect();
    }

    
    /**
     * Get an instance of this object
     * NOTE: You need to created this function for each inherited class
     *
     * @return Com_Util_BotDetector
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    /**
     * Detect if the user is a bot or a real person
     *
     */
    private function detect()
    {
        $bots = array();
        $userAgent = Tk_Request::agent();
        $botsIni = Tk_Config::getSitePath() . '/bots.ini';
        if (is_file($botsIni)) {
            $bots = parse_ini_file($botsIni);
        }
        $bots['Google'] = 'googlebot';
        $bots['MSN-Bot'] = 'msnbot';
        $bots['Yahoo'] = 'yahoo';
        $bots['Unknown Bot'] = 'bot';
        $bots['Unknown Spider'] = 'spider';
        
        foreach ($bots as $botName => $botReg) {
            if (preg_match('/' . $botReg . '/i', $userAgent)) {
                $this->botName = $botName;
                break;
            }
        }
        // Assume is bot if no agent string
        if ($userAgent == null) {
            $this->botName = 'Unknown Agent String';
        }
    }
    
    /**
     * is the user a bot
     *
     * @return boolean
     */
    static function isBot()
    {
        if (self::getInstance()->botName) {
            return true;
        }
        return false;
    }
    
    /**
     * Get the bot name if detected
     *
     * @return string
     */
    static function getBotName()
    {
        return self::getInstance()->botName;
    }
}
