<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Base plugin object. Use this object to 
 * create your own MCE plugin wrappers.
 *
 * @package Mce
 */
abstract class Js_Mce_Plugin extends Tk_Object
{
    
    /**
     * @var string
     */
    protected $name = '';
    
    /**
     * @var Js_Mce
     */
    protected $mce = null;
    
    /**
     * The plugin folder path relative to the data directory
     * @var string
     */
    private $pluginPath = '/tinymce/jscripts/tiny_mce/plugins';
    
    
    
    /**
     * __construct
     * 
     * @param Js_Mce $mce
     * @param string $name
     */
    function __construct($name)
    {
        $this->name = $name;
        if (Tk_Config::isDebugMode() || !is_dir(Tk_Config::getDataPath() . $this->pluginPath . '/' . $name)) {
            $this->install();
        }
    }
    
    /**
     * Install teh plugin to the tinymce folder.
     * Please ensure the plugin source resides in the same folder as teh plugin class
     * and the plugin name is teh same as the plugin source directory
     * 
     * @return boolean
     */
    private function install()
    {
        $shell = new Tk_Util_Exec();
        $arr = explode('_', get_Class($this));
        array_pop($arr);
        $srcPath = '/' . implode('/', $arr);
        
        // Still working on the zip
//        $compress = sprintf('cd %s && zip -rq %s.zip ./%s ', Tk_Config::getLibPath() . $srcPath, Tk_Config::getDataPath() . '/tinymce/jscripts/tiny_mce/plugins/' . $this->getName(), $this->getName() );
//        $extract = sprintf('cd %s && unzip %s.zip ', Tk_Config::getDataPath() . '/tinymce/jscripts/tiny_mce/plugins/', $this->getName());
//        $del = sprintf('rm -f %s.zip ', Tk_Config::getDataPath() . '/tinymce/jscripts/tiny_mce/plugins/' . $this->getName());
        
        //$compress = sprintf('cd %s && tar --exclude=\'.svn\' -z -c -f -m %s.tgz ./%s ', Tk_Config::getLibPath() . $srcPath, Tk_Config::getDataPath() . '/tinymce/jscripts/tiny_mce/plugins/' . $this->getName(), $this->getName() );
        $compress = sprintf('cd %s && tar zcfp %s.tgz ./%s ', Tk_Config::getLibPath() . $srcPath, Tk_Config::getDataPath() . '/tinymce/jscripts/tiny_mce/plugins/' . $this->getName(), $this->getName() );
        $extract = sprintf('cd %s && tar zxfp %s.tgz', Tk_Config::getDataPath() . '/tinymce/jscripts/tiny_mce/plugins/', $this->getName());
        $del = sprintf('rm -f %s.tgz', Tk_Config::getDataPath() . '/tinymce/jscripts/tiny_mce/plugins/' . $this->getName());
        
        try {
            $msg = $shell->exec($compress);
            $msg = $shell->exec($extract);
            $msg = $shell->exec($del);
        } catch (Exception $e) {
            vd($e, $msg);
            throw new Tk_Exception('Please manually install the tinyMCE plugin `' . $this->getName() . '`.');
        }
    }
    
    
    /**
     * Execute the plugin
     */
    abstract function init();
    
    
    /**
     * Get the plugin name
     * 
     * @return string 
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     * Set the parent Mce object
     * 
     * @param Js_Mce $mce 
     * @return Js_Mce_Plugin
     */
    function setMce(Js_Mce $mce) 
    {
        $this->mce = $mce;
        return $this;
    }
    
    /**
     * Get the Mce object
     * 
     * @return Js_Mce 
     */
    function getMce()
    {
        return $this->mce;
    }
    
    
    
    
}
