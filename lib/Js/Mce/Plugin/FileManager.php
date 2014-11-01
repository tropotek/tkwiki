<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * TinyMce File Manager Plugin Wrapper
 * 
 * @package Mce
 */
class Js_Mce_Plugin_FileManager extends Js_Mce_Plugin
{
    
    protected $dataPath = '';
    
    
    /**
     * Create a new Filemanager object
     * 
     * @param string $dataPath The root file manager folder
     * @return Js_Mce_Plugin_FileManager
     */
    static function create($dataPath = '')
    {
        $obj = new self('fileManager');
        if ($dataPath) {
            $obj->dataPath = $dataPath;
        } else {
            $obj->dataPath = Tk_Config::getDataPath() . '/fileManager';
        }
        return $obj;
    }
    
    /**
     * init
     */
    function init()
    {
        $this->getMce()->addButton($this->getName(), 0, 0);
        // NOTE: By using this method we can only have one global fileManager directory
        // per page. change if we can find a solution to handle individual instances.
        Tk_Session::set('js.tinymce.fileManagerPath', $this->dataPath);
        Tk_Session::set('js.tinymce.mcePath', Tk_Config::getDataPath() . $this->mce->getSourcePath());
        
    }
    
}
