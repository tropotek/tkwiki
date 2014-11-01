<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A config/registry object that configures the Sdk functionality.
 * 
 * @package Com
 * @deprecated
 */
class Com_Config extends Tk_Config
{
    
    /**
     * Get an instance of this object
     *
     * @return Com_Config
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    
    
    /**
     * Set the admin page array.
     * This array is used as an admin url lookup map to match modules with pages
     *
     * @param array $array
     * @default array()
     */
    static function setDynamicPages($array)
    {
        self::set('system.url.pages', $array);
    }
    
    /**
     * Get the admin page array
     * This array is used as an admin url lookup map to match modules with pages
     *
     * @return array
     * @default array()
     */
    static function getDynamicPages()
    {
        return self::get('system.url.pages');
    }
    
    /**
     * Add a page to the admin pages list
     *
     * @param string $requestPath
     * @param Com_Web_MetaData $metaData
     * @param boolean $overwrite If false then the page will not be added if one exists
     */
    static function addDynamicPage($requestPath, $metaData, $overwrite = true)
    {
        $arr = self::getDynamicPages();
        if (!is_array($arr)) {
            $arr = array();
        }
        if ($requestPath[0] != '/' && $requestPath[0] != '\\') {
        	$requestPath = '/' . $requestPath;
        }
        if($overwrite || (!$overwrite && !isset($arr[$requestPath])) ) {
        	$arr[$requestPath] = $metaData;
        }
        self::setDynamicPages($arr);
    }
    
    /**
     * Add a page to the admin pages list
     *
     * @param string $requestPath
     */
    static function removeDynamicPage($requestPath)
    {
        $arr = self::getDynamicPages();
        if (!is_array($arr)) {
            return $this;
        }
        if ($requestPath[0] != '/' && $requestPath[0] != '\\') {
            $requestPath = '/' . $requestPath;
        }
        if (array_key_exists($requestPath, $arr)) {
            unset($arr[$requestPath]);
            self::setDynamicPages($arr);
        }
    }
    
    
    

    
    /**
     * Get this sites html template directory
     *
     * @return string
     * @default $fileRoot.'/html'
     * @deprecated
     */
    static function getHtmlTemplates()
    {
        return self::getTemplatePath();
    }
    
    /**
     * Set this sites html template directory
     * If this is changed remember to modify the .htaccess file as well
     *
     * @param string $path
     * @default $fileRoot.'/html'
     */
    static function setTemplatePath($path)
    {
        self::set('system.templatePath', $path);
    }
    
    /**
     * Get this sites html template directory
     * If this is changed remember to modify the .htaccess file as well
     *
     * @return string
     * @default $fileRoot.'/html'
     */
    static function getTemplatePath()
    {
        return self::get('system.templatePath');
    }
    
    /**
     * Set this to true if this site has an SSL cert installed
     * NOTE: This will not work for shared SSL or SSL on a different url/path
     *
     * @param boolean $b
     * @default false
     */
    static function setSslEnabled($b)
    {
        self::set('system.ssl', $b == true);
    }
    
    /**
     * Return true if this site has an SSL cert installed
     * NOTE: This will not work for shared SSL or SSL on a different url/path
     *
     * @return boolean
     * @default false
     */
    static function isSslEnabled()
    {
        return self::get('system.ssl');
    }
    
    /**
     * Set Language using the i18n codes
     *
     * @param string $str
     * @default en_GB (english)
     */
    static function setLanguage($str)
    {
        self::set('system.lang.local', strtolower($str));
    }
    
    /**
     * Get Language using the i18n codes
     *
     * @return string
     * @default en_GB (english)
     */
    static function getLanguage()
    {
        return self::get('system.lang.local');
    }
    
    /**
     * Set the admin url path folder.
     * Default is '/admin', this sets the admin URL to 'http://www.domain.com/admin'
     *
     * @param string $str
     * @default '/admin'
     */
    static function setAdminPath($str)
    {
        self::set('system.admin.path', $str);
    }
    
    /**
     * Get The relatice admin path.
     * If this is null then no admin template system is enabled
     *
     * @return string
     * @default '/admin'
     */
    static function getAdminPath()
    {
        return self::get('system.admin.path');
    }
    
    
    
    /**
     * Set the user hash function
     * This can be one of PHP's functions such as 'md5', 'sha1' or user defined
     * If null then no hash is used on passwords
     *
     * @param string $str
     * @default NULL
     */
    static function setUserHashFunction($str)
    {
        self::set('com.auth.hashFunction', $str);
    }
    
    /**
     * Get the user hash function
     * This can be one of PHP's functions such as 'md5', 'sha1' or user defined
     * If null then no hash is used on passwords
     *
     * @return string
     * @default NULL
     */
    static function getUserHashFunction()
    {
        return self::get('com.auth.hashFunction');
    }
    
    /**
     * Set MasterKey
     *
     * @param string $str
     * @default Off for opensource projects
     */
    static function setMasterKey($str)
    {
        self::set('com.auth.masterKey', $str);
    }
    
    /**
     * Get MasterKey
     *
     * @return string
     * @default Off for opensource projects
     */
    static function getMasterKey()
    {
        return self::get('com.auth.masterKey');
    }
    

}