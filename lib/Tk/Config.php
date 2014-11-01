<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A config/registry object that configures the Tk functionality.
 *
 * Common Config Sections:
 *  o system: Any system runtime settings.
 *  o debug: Any debug config settings
 *  o database.[name]: Database settings, can have multiple, `default` is used by default.
 *
 * See the local lib config folder for any library specific configurations, such as cookie.php, session.php
 * These can be overridden in your own /lib/config folder...
 *
 * @package Tk
 */
class Tk_Config extends Tk_Util_Registry
{
    /**
     * @var Tk_Config
     */
    static $instance = null;
    
    static $parsed = false;
    
    /**
     * Get an instance of this object
     * NOTE: You need to created this function for each inherited class
     *
     * @return Tk_Config
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    
    /**
     * Test if the system has parsed the config file.
     * 
     */
    static function isParsed()
    {
    	return self::$parsed;
    }
    
    /**
     * (non-PHPdoc)
     * @see Tk_Util_Registry::parseConfigFile()
     */
    function parseConfigFile($file, $prependKey = '',  $overwrite = true)
    {
        parent::parseConfigFile($file, $prependKey, $overwrite);        
    	self::$parsed = true;
    }
    
    
    
    /**
     * Set an entry into the registry cache
     *
     * @param string $key
     * @param object $value
     */
    static function set($key, $value)
    {
        return self::getInstance()->setEntry($key, $value);
    }
    
    /**
     * Return an entry from the registry cache
     *
     * @param string $key
     * @return object
     */
    static function get($key)
    {
        return self::getInstance()->getEntry($key);
    }
    
    /**
     * Test if an entry exists and is not null
     *
     * @param string $key
     * @return boolean
     */
    static function exists($key)
    {
        return self::getInstance()->entryExists($key);
    }
    
    /**
     * Get a section of the config.ini that is defined by [..] brackets
     * This is usually used to setup a Widget system config options
     *
     * @param string $section
     * @return array
     *  No onger used, use dot notation and get the section using Tk_Config::get('system'); which would return all system config options.
     */
    static function getSectionConfig($section = '')
    {
        $ini = array();
        if ($section) {
            $ini = parse_ini_file(self::getInstance()->getSitePath() . '/config.ini', true);
        } else {
            $ini = parse_ini_file(self::getInstance()->getSitePath() . '/config.ini');
        }
        if (isset($ini[$section])) {
            return $ini[$section];
        }
        return $ini;
    }
    
    
    
    /**
     * Set the site's default title which will be used for emails, notices, titles etc
     *
     * @param string $name
     * @default 'Untitled Site'
     * @deprecated
     */
    static function setSiteTitle($name)
    {
        self::set('system.siteTitle', $name);
    }
    
    /**
     * Get the site's default title which will be used for emails, notices, titles etc
     *
     * @return string
     * @default 'Untitled Site'
     * @deprecated
     */
    static function getSiteTitle()
    {
        return self::get('system.siteTitle');
    }
    

    
    
    /**
     * Set this sites htdoc root path.
     * Note: this must be set if no FrontController is used with modrewrite
     * Example: /~user/project/
     *
     * @param string $path
     * @default $_SERVER['PHP_SELF']
     *
     */
    static function setHtdocRoot($path)
    {
        self::set('system.htroot', $path);
    }
    
    /**
     * Get this sites htdoc root path.
     * Note: this must be set if no FrontController is used with modrewrite
     * Example: /~user/project/
     *
     * @return string
     * @default $_SERVER['PHP_SELF']
     *
     */
    static function getHtdocRoot()
    {
        return self::get('system.htroot');
    }
    
    /**
     * Get this sites htdoc root path.
     * Note: this must be set if no FrontController is used with modrewrite
     * Example: /~user/project/
     *
     * @return string
     * @default $_SERVER['PHP_SELF']
     *
     */
    static function getSiteUrl()
    {
        return self::get('system.htroot');
    }
    
    /**
     * Set this sites filesystem root path.
     * Example: /home/user/public_html/project/
     *
     * @param string $path
     * @default dirname() of the site index.php file
     *
     */
    static function setSitePath($path)
    {
        self::set('system.sitePath', $path);
    }
    
    /**
     * Get this sites filesystem root path.
     * Example: /home/user/public_html/project
     *
     * @return string
     * @default dirname() of the site index.php file
     *
     */
    static function getSitePath()
    {
        return self::get('system.sitePath');
    }
    
    /**
     * Set this sites filesystem root path.
     * Example: /home/user/public_html/project/lib
     *
     * @param string $path
     * @default dirname().'/lib' of the site index.php file
     *
     */
    static function setLibPath($path)
    {
        self::set('system.libPath', $path);
    }
    
    /**
     * Get this sites filesystem root path.
     * Example: /home/user/public_html/project/lib
     *
     * @return string
     * @default dirname().'/lib' of the site index.php file
     */
    static function getLibPath()
    {
        return self::get('system.libPath');
    }
    
    /**
     * Set the Data directory for this site.
     * NOTE: This directory must be writable or you will see a warning on the page
     *
     * @param string $path
     * @default getSitePath().'/data'
     */
    static function setDataPath($path)
    {
        self::set('system.dataPath', $path);
    }
    
    /**
     * Get the data directory for this site
     *
     * @return string
     * @default getSitePath().'/data'
     */
    static function getDataPath()
    {
        return self::get('system.dataPath');
    }
    
    /**
     * Set the Data Url for this site, only the path is required.
     * EG: '/data'
     *
     * @param string $path
     * @default Auto created from the site url and the default data path
     */
    static function setDataUrl($path)
    {
        self::set('system.dataUrl', $path);
    }
    
    /**
     * Get the base data url for this site, only the path is returned
     * EG: '/data'
     *
     * @return string
     * @default Auto created from the site url and the default data path
     */
    static function getDataUrl()
    {
        return self::get('system.dataUrl');
    }
    
    
    /**
     * Set the apllication tmp dir
     *
     * @param string $dir
     * @default getDataPath().'/tmp'
     * @deprecated
     */
    static function setTmpPath($dir)
    {
        self::set('system.tmpPath', $dir);
        ini_set('upload_tmp_dir', $dir);
    }
    
    /**
     * Get the apllication tmp dir
     *
     * @return string
     * @default getDataPath().'/tmp'
     * @deprecated
     */
    static function getTmpPath()
    {
        return self::get('system.tmpPath');
    }
    
    
    
    
    
    
    
    
    
    
    /**
     * Set the host for the database libs
     *
     * @param string $host
     * @default null
     * @deprecated
     */
    static function setDbHost($host)
    {
        self::set('database.default.host', $host);
    }
    
    /**
     * Get the host for the database.
     *
     * @return string
     * @default null
     * @deprecated
     */
    static function getDbHost()
    {
        return self::get('database.default.host');
    }
    
    /**
     * Set the user for the database.
     *
     * @param string $user
     * @default null
     * @deprecated
     */
    static function setDbUser($user)
    {
        self::set('database.default.user', $user);
    }
    
    /**
     * Get the database user.
     *
     * @return string
     * @default null
     * @deprecated
     */
    static function getDbUser()
    {
        return self::get('database.default.user');
    }
    
    /**
     * Set Database password.
     *
     * @param string $password
     * @default null
     * @deprecated
     */
    static function setDbPassword($password)
    {
        self::set('database.default.password', $password);
    }
    
    /**
     * Get the database password.
     *
     * @return string
     * @default null
     * @deprecated
     */
    static function getDbPassword()
    {
        return self::get('database.default.password');
    }
    
    /**
     * Set the database name
     *
     * @param string $database
     * @default null
     * @deprecated
     */
    static function setDbDatabase($database)
    {
        self::set('database.default.name', $database);
    }
    
    /**
     * Get the database name
     *
     * @return string
     * @default null
     * @deprecated
     */
    static function getDbDatabase()
    {
        return self::get('database.default.name');
    }
    
    
    
    
    
    /**
     * Set this site to use debug mode.
     * Debug mode should be set to false for live sites.
     *
     * Turns on:
     *  o Replace relative paths to home directory
     *  o Log errors E_ALL|E_STRICT
     *
     * @param boolean $b
     * @default flase
     */
    static function setDebugMode($b)
    {
        self::set('debug.enable', $b);
    }
    
    /**
     * Return true if this site is in debug mode
     *
     * @return boolean
     * @default flase
     */
    static function isDebugMode()
    {
        return self::get('debug.enable');
    }
    
    
    
    
    
    
    /**
     * Set the error log file to use.
     * This will only works if Debug mode is enabled first
     * For a live site we want to use the default system log file.
     *
     * @param string $file
     * @default Use system error log
     * @deprecated
     */
    static function setErrorLog($file)
    {
        self::set('system.log', $file);
    }
    
    /**
     * Get the error log file path.
     *
     * @return string
     * @default Use system error log
     * @deprecated
     */
    static function getErrorLog()
    {
        return self::get('system.log');
    }
    
    /**
     * When in debug mode this email is used
     * When used in cli mode the command `hostname` is used in place of `HTTP_HOST`
     *
     * @param string $email
     * @default info@{HTTP_HOST}
     * @deprecated
     */
    static function setDebugEmail($email)
    {
        self::set('debug.email', $email);
    }
    
    /**
     * When in debug mode this email is used
     *
     * @return string
     * @default info@{HTTP_HOST}
     * @deprecated
     */
    static function getDebugEmail()
    {
        return self::get('debug.email');
    }
    
    /**
     * Set the timezone for php.
     * See http://au.php.net/manual/en/timezones.php for a valad list of timezones
     *
     * @param string $str
     * @default Australia/Queensland
     * @deprecated
     */
    static function setTimezone($str)
    {
        self::set('system.timezone', $str);
    }
    
    /**
     * Get the current timezone
     * See http://au.php.net/manual/en/timezones.php for a valad list of timezones
     *
     * @return string
     * @default Australia/Queensland
     * @deprecated
     */
    static function getTimezone()
    {
        return self::get('system.timezone');
    }
    
    /**
     * Set the default currency for the Tk_Util_Money object
     * Valid Values: AUD, NZD, USD, THB
     *
     * @param string $str
     * @default 'AUD'
     * @deprecated
     */
    static function setCurrency($str)
    {
        self::set('system.currency', $str);
    }
    
    /**
     * Get the default currency for the Tk_Util_Money object
     * Valid Values: AUD, NZD, USD, THB
     *
     * @return string
     * @default 'AUD'
     * @deprecated 
     */
    static function getCurrency()
    {
        return self::get('system.currency');
    }
    
    /**
     * Set the opensource status of this project.
     * This will stop certin code from executing in opencource projects
     * as this code is not required for opensource projects.
     *
     * NOTE: The code in the non-opensource control structures where
     * isOpensource() == false may not be GPL and therfore should not be modified
     * in any way.
     *
     * @param boolean $b
     * @default true
     * @deprecated
     */
    static function setOpenSource($b)
    {
        self::set('system.openSource', $b == true);
    }
    
    /**
     * Get the opensource status of this project.
     * This will stop certin code from executing in opencource projects
     * as this code is not required for opensource projects.
     *
     * NOTE: The code in the non-opensource control structures where
     * isOpensource() == false may not be GPL and therfore should not be modified
     * in any way.
     *
     * @return boolean
     * @default true
     * @deprecated
     */
    static function isOpenSource()
    {
        return self::get('system.openSource');
    }

    
    /**
     * Set SupportEmail
     *
     * @param string $str
     * @deprecated
     */
    static function setSupportEmail($str)
    {
        self::set('system.supportEmail', $str);
    }
    
    /**
     * Get SupportEmail
     *
     * @return string
     * @deprecated
     */
    static function getSupportEmail()
    {
        return self::get('system.supportEmail');
    }
    
    
}