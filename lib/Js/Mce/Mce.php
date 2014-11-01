<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * This script is to setup the TinyMCE javascript WYSIWYG Editor
 *
 * @package Ui
 */
class Js_Mce extends Dom_Renderer
{
    
    /**
     * @var string 
     */
    protected $selector = 'textarea';
    
    /**
     * The theme [advanced|simple]
     * @var string 
     */
    protected $theme = 'advanced';
    
    /**
     * The plugins to load with this instance of TinyMCE
     * @var array
     */
    protected $pluginList = array();
    
    /**
     * A multidimensional array of 4 rows of buttons
     * @var array 
     */
    protected $buttonList = array();
    
    /**
     * @var array 
     */
    protected $paramList = array();
    
    /**
     * Path to source files relative to the data path
     * @var string 
     */
    protected $sourcePath = '/tinymce/jscripts/tiny_mce';
    
    protected $mceFile = 'tinymce_3.4.7_jquery.zip';
    
    
    
    /**
     * __construct
     *
     * 
     */
    function __construct($selector = 'textarea')
    {
        $this->setSelector($selector);
        $this->buttonList = array(array(), array(), array(), array());
        
        // Unzip tiny mce package if required.
        $srcPath = Tk_Config::getdataPath() . $this->sourcePath;
        if (Tk_Config::isDebugMode() || !is_dir($srcPath)) {
            $shell = new Tk_Util_Exec();
            $cmd = sprintf("unzip -o %s/lib/Js/Mce/%s -d %s", Tk_Config::getSitePath(), $this->mceFile, Tk_Config::getDataPath());
            try {
                $shell->exec($cmd);
            } catch (Exception $e) {
                vd("Please manually unzip TinyMCE ([sitePath]/lib/Js/Mce/{$this->mceFile}) to `" . Tk_Config::getDataPath() . '`.');
                //throw new Tk_ExceptionRuntime('Please manually unzip TinyMCE ([sitePath]/lib/Js/Mce{$this->mceFile}) to `' . Tk_Config::getDataPath() . '`.');
            }
        }
    }
    
    /**
     *
     * @param string $selector
     * @return Js_Mce
     */
    function setSelector($selector)
    {
        $this->selector = $selector;
        return $this;
    }
    
    /**
     * Get the source path relative to the data directory
     * you need to prepend the data path to get a full path.
     * 
     * @return string
     */
    function getSourcePath()
    {
        return $this->sourcePath;
    }
    
    /**
     * createSimple
     * 
     * @return Js_Mce
     */
    static function createSimple($selector = 'textarea')
    {
        $obj = new self($selector);
        $obj->addPlugin('inlinepopups');
        $obj->addPlugin('safari');
        
        $obj->addButton('bold');
        $obj->addButton('italic');
        $obj->addButton('underline');
        $obj->addButton('strikethrough');
        $obj->addButton('|');
        $obj->addButton('link');
        $obj->addButton('unlink');
        $obj->addButton('image');
        $obj->addButton('code');
        
        $obj->addPlugin(Js_Mce_Plugin_PreElementFix::create());
        return $obj;
    }
    
    /**
     * createBasic
     * 
     * @return Js_Mce
     */
    static function createBasic($selector = 'textarea')
    {
        $obj = new self($selector);
        $obj->addPlugin('inlinepopups');
        $obj->addPlugin('safari');
        $obj->addPlugin('directionality');
        
        $obj->addButton('bold');
        $obj->addButton('italic');
        $obj->addButton('underline');
        $obj->addButton('strikethrough');
        $obj->addButton('|');
        $obj->addButton('justifyleft');
        $obj->addButton('justifycenter');
        $obj->addButton('justifyright');
        $obj->addButton('justifyfull');
        $obj->addButton('|');
        $obj->addButton('bullist');
        $obj->addButton('numlist');
        $obj->addButton('outdent');
        $obj->addButton('indent');
        $obj->addButton('|');
        $obj->addButton('link');
        $obj->addButton('unlink');
        $obj->addButton('image');
        $obj->addButton('|');
        $obj->addButton('charmap');
        $obj->addButton('removeformat');
        $obj->addButton('cleanup');
        $obj->addButton('code');
        
        $obj->addPlugin(Js_Mce_Plugin_PreElementFix::create());
        return $obj;
    }
    
    /**
     * 
     * @return Js_Mce
     */
    static function createNormal($selector = 'textarea')
    {
        $obj = new self($selector);
        $obj->addPlugin('inlinepopups');
        $obj->addPlugin('safari');
        $obj->addPlugin('table');
        $obj->addPlugin('paste');
        $obj->addPlugin('save');
        $obj->addPlugin('searchreplace');
        $obj->addPlugin('advhr');
        $obj->addPlugin('advimage');
        $obj->addPlugin('advlink');
        $obj->addPlugin('insertdatetime');
        $obj->addPlugin('media');
        $obj->addPlugin('fullscreen');
        $obj->addPlugin('visualchars');
        $obj->addPlugin('nonbreaking');
        $obj->addPlugin('directionality');
        $obj->addPlugin('emotions');
        $obj->addPlugin('xhtmlxtras');
        $obj->addPlugin('noneditable');
        
        $row = 0;
        $obj->addButton('bold', $row);
        $obj->addButton('italic', $row);
        $obj->addButton('underline', $row);
        $obj->addButton('strikethrough', $row);
        $obj->addButton('|', $row);
        $obj->addButton('justifyleft', $row);
        $obj->addButton('justifycenter', $row);
        $obj->addButton('justifyright', $row);
        $obj->addButton('justifyfull', $row);
        $obj->addButton('|', $row);
        $obj->addButton('bullist', $row);
        $obj->addButton('numlist', $row);
        $obj->addButton('outdent', $row);
        $obj->addButton('indent', $row);
        $obj->addButton('|', $row);
        $obj->addButton('formatselect', $row);
        $obj->addButton('fontsizeselect', $row);
        $obj->addButton('forecolor', $row);
        
        $row = 1;
        $obj->addButton('pasteword', $row);
        $obj->addButton('|', $row);
        $obj->addButton('search', $row);
        $obj->addButton('replace', $row);
        $obj->addButton('|', $row);
        $obj->addButton('advhr', $row);
        $obj->addButton('insertdate', $row);
        $obj->addButton('inserttime', $row);
        $obj->addButton('emotions', $row);
        $obj->addButton('|', $row);
        $obj->addButton('image', $row);
        $obj->addButton('media', $row);
        $obj->addButton('|', $row);
        $obj->addButton('sub', $row);
        $obj->addButton('sup', $row);
        $obj->addButton('|', $row);
        $obj->addButton('link', $row);
        $obj->addButton('unlink', $row);
        $obj->addButton('anchor', $row);
        $obj->addButton('|', $row);
        $obj->addButton('fullscreen', $row);
        $obj->addButton('charmap', $row);
        $obj->addButton('removeformat', $row);
        $obj->addButton('cleanup', $row);
        $obj->addButton('code', $row);
        $obj->addButton('|', $row);
        $obj->addButton('help', $row);
        /*
        $row = 2;
        $obj->addButton('tablecontrols', $row);
        $obj->addButton('|', $row);
        $obj->addButton('visualaid', $row);
        $obj->addButton('|', $row);
        $obj->addButton('cite', $row);
        $obj->addButton('abbr', $row);
        $obj->addButton('acronym', $row);
        $obj->addButton('del', $row);
        $obj->addButton('ins', $row);
        $obj->addButton('|', $row);
        $obj->addButton('visualchars', $row);
        $obj->addButton('nonbreaking', $row);
        */
        $obj->addPlugin(Js_Mce_Plugin_PreElementFix::create());
        
        $obj->addParam('save_onsavecallback', 'function() { $(window).unbind(\'beforeunload\'); submitForm(document.forms[0], \'save\'); }');
        $obj->addParam('save_oncancelcallback', 'function() { $(window).unbind(\'beforeunload\'); submitForm(document.forms[0], \'cancel\'); }');
        
        
        return $obj;
    }
    
    /**
     * 
     * @return Js_Mce
     */
    static function createFull($selector = 'textarea')
    {
        $obj = new self($selector);
        $obj->addPlugin('inlinepopups');
        $obj->addPlugin('safari');
        $obj->addPlugin('pagebreak');
        $obj->addPlugin('style');
        $obj->addPlugin('layer');
        $obj->addPlugin('table');
        $obj->addPlugin('save');
        $obj->addPlugin('advhr');
        $obj->addPlugin('advimage');
        $obj->addPlugin('advlink');
        $obj->addPlugin('emotions');
        $obj->addPlugin('iespell');
        $obj->addPlugin('insertdatetime');
        $obj->addPlugin('preview');
        $obj->addPlugin('media');
        $obj->addPlugin('searchreplace');
        $obj->addPlugin('print');
        $obj->addPlugin('paste');
        $obj->addPlugin('directionality');
        $obj->addPlugin('fullscreen');
        $obj->addPlugin('noneditable');
        $obj->addPlugin('visualchars');
        $obj->addPlugin('nonbreaking');
        $obj->addPlugin('xhtmlxtras');
        $obj->addPlugin('template');
        
        
        $row = 0;
        $obj->addButton('save', $row);
        $obj->addButton('newdocument', $row);
        $obj->addButton('|', $row);
        $obj->addButton('bold', $row);
        $obj->addButton('italic', $row);
        $obj->addButton('underline', $row);
        $obj->addButton('strikethrough', $row);
        $obj->addButton('|', $row);
        $obj->addButton('justifyleft', $row);
        $obj->addButton('justifycenter', $row);
        $obj->addButton('justifyright', $row);
        $obj->addButton('justifyfull', $row);
        $obj->addButton('|', $row);
        $obj->addButton('styleselect', $row);
        $obj->addButton('formatselect', $row);
        $obj->addButton('fontselect', $row);
        $obj->addButton('fontsizeselect', $row);
        
        $row = 1;
        $obj->addButton('cut', $row);
        $obj->addButton('copy', $row);
        $obj->addButton('paste', $row);
        $obj->addButton('pastetext', $row);
        $obj->addButton('pasteword', $row);
        $obj->addButton('|', $row);
        $obj->addButton('search', $row);
        $obj->addButton('replace', $row);
        $obj->addButton('|', $row);
        $obj->addButton('bullist', $row);
        $obj->addButton('numlist', $row);
        $obj->addButton('outdent', $row);
        $obj->addButton('indent', $row);
        $obj->addButton('blockquote', $row);
        $obj->addButton('|', $row);
        $obj->addButton('undo', $row);
        $obj->addButton('redo', $row);
        $obj->addButton('|', $row);
        $obj->addButton('link', $row);
        $obj->addButton('unlink', $row);
        $obj->addButton('anchor', $row);
        $obj->addButton('image', $row);
        $obj->addButton('media', $row);
        $obj->addButton('|', $row);
        $obj->addButton('forecolor', $row);
        $obj->addButton('backcolor', $row);
        $obj->addButton('|', $row);
        $obj->addButton('ltr', $row);
        $obj->addButton('rtl', $row);
        
        $row = 2;
        $obj->addButton('tablecontrols', $row);
        $obj->addButton('|', $row);
        $obj->addButton('visualaid', $row);
        $obj->addButton('|', $row);
        $obj->addButton('sub', $row);
        $obj->addButton('sup', $row);
        $obj->addButton('|', $row);
        $obj->addButton('cite', $row);
        $obj->addButton('abbr', $row);
        $obj->addButton('acronym', $row);
        $obj->addButton('del', $row);
        $obj->addButton('ins', $row);
        $obj->addButton('attribs', $row);
        $obj->addButton('|', $row);
        $obj->addButton('visualchars', $row);
        $obj->addButton('nonbreaking', $row);
        $obj->addButton('|', $row);
        $obj->addButton('print', $row);
        $obj->addButton('iespell', $row);
        
        $row = 3;
        $obj->addButton('insertlayer', $row);
        $obj->addButton('moveforward', $row);
        $obj->addButton('movebackward', $row);
        $obj->addButton('absolute', $row);
        $obj->addButton('|', $row);
        $obj->addButton('styleprops', $row);
        $obj->addButton('|', $row);
        $obj->addButton('template', $row);
        $obj->addButton('pagebreak', $row);
        $obj->addButton('|', $row);
        $obj->addButton('insertdate', $row);
        $obj->addButton('inserttime', $row);
        $obj->addButton('|', $row);
        $obj->addButton('advhr', $row);
        $obj->addButton('emotions', $row);
        $obj->addButton('|', $row);
        $obj->addButton('charmap', $row);
        $obj->addButton('removeformat', $row);
        $obj->addButton('cleanup', $row);
        $obj->addButton('code', $row);
        $obj->addButton('|', $row);
        $obj->addButton('fullscreen', $row);
        $obj->addButton('help', $row);
        
        $obj->addPlugin(Js_Mce_Plugin_PreElementFix::create());
        
        $obj->addParam('save_onsavecallback', 'function() { $(window).unbind(\'beforeunload\'); submitForm(document.forms[0], \'save\'); }');
        $obj->addParam('save_oncancelcallback', 'function() { $(window).unbind(\'beforeunload\'); submitForm(document.forms[0], \'cancel\'); }');
        
        return $obj;
    }
    
    /**
     * show
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        Js_Ui_Jquery::create($template);
        
        $jsUrl = Tk_Type_Url::create(Tk_Config::getDataUrl() . $this->sourcePath . '/jquery.tinymce.js');
        $template->appendJsUrl($jsUrl->toString());
        
        $url = Tk_Type_Url::create(Tk_Config::getDataUrl() . $this->sourcePath . '/tiny_mce.js');
        $js = <<<JS
$(function() {
  $('{$this->selector}').tinymce({
    script_url: '{$url->toString()}',
    theme: '{$this->theme}',
    skin: 'o2k7',
    plugins: '{$this->getPluginStr()}',
    theme_advanced_buttons1: '{$this->getButtonStr(0)}',
    theme_advanced_buttons2: '{$this->getButtonStr(1)}',
    theme_advanced_buttons3: '{$this->getButtonStr(2)}',
    theme_advanced_buttons4: '{$this->getButtonStr(3)}',
                        
    theme_advanced_toolbar_location : 'top',
    theme_advanced_toolbar_align: 'left',
    theme_advanced_statusbar_location: 'bottom',
    theme_advanced_resizing: true,
    convert_urls: false,
    relative_urls: false
    {$this->getParamStr()}
  });
});
JS;
        $template->appendJs($js);
    }
    
    /**
     * getParamStr
     * 
     * @return string 
     */
    protected function getParamStr()
    {
        $str = '';
        foreach ($this->paramList as $k => $v) {
            $str .= ",\n    " . $k .': ' . $v;
        }
        return $str;
    }
    
    /**
     * getPluginStr
     * Remember to add quotes to the param value if required
     * 
     * @return string 
     */
    protected function getPluginStr()
    {
        $js = '';
        foreach ($this->pluginList as $k => $v) {
            if($v instanceof Js_Mce_Plugin) {
                continue;
            }
            $js .= $k . ',';
        }
        if ($js) {
            $js = substr($js, 0, -1);
        }
        return $js;
    }
    
    /**
     * Get the button list for a row of buttons
     * 
     * @return string 
     */
    protected function getButtonStr($row)
    {
        $row = $this->cleanRowIdx($row);
        $str = '';
        foreach ($this->buttonList[$row] as $v) {
            $str .= $v . ',';
        }
        if ($str) {
            $str = substr($str, 0, -1);
        }
        return $str;
    }
    
    /**
     * If the plugin comes bundled with tinymce you only
     * need to pass the plugin name as a string.
     * However you can create custom plugins subclassing
     * the Mce_Plugin object, that can be used as a parameter also.
     * 
     * @var mixed $plugin
     * @return Js_Mce
     */
    function addPlugin($plugin)
    {
        if ($plugin instanceof Js_Mce_Plugin) {
            $plugin->setMce($this);
            $plugin->init();
            $this->pluginList[$plugin->getName()] = $plugin->getName();
            return $this;
        }
        $this->pluginList[$plugin] = $plugin;
        return $this;
    }
    
    /**
     * Add a button to the button queue in a position that you select
     * 
     * @param string $name
     * @param integer $row Numbered from 0-3
     * @param integer $pos
     * @return Js_Mce
     */
    function addButton($name, $row = 0, $pos = null) 
    {
        $row = $this->cleanRowIdx($row);
        $pos = $this->cleanPosIdx($row, $pos);
        
        if ($pos === null || $pos >= count($this->buttonList[$row])) { 
            $this->buttonList[$row][] = $name;
        } else if ($pos == 0) {
            array_unshift($this->buttonList[$row], $name);
        } else {
            $arr1 = array_slice($this->buttonList[$row], 0, $pos);
            $arr2 = array_slice($this->buttonList[$row], $pos+1);
            $arr1[] = $name;
            $this->buttonList[$row] = array_merge($arr1, $arr2);
        }
        return $this;
    }
    
    /**
     * Remove a button from the list
     * If the values are outside the list lengths no buttons will be removed
     *
     * @param type $row
     * @param type $pos
     * @return Js_Mce 
     */
    function removeButton($row, $pos)
    {
        if (isset($this->buttonList[$row][$pos])) {
            unset($this->buttonList[$row][$pos]);
            $this->buttonList[$row] = array_merge($this->buttonList[$row]);
        }
        return $this;
    }
    
    /**
     * Get the end of a button list array
     *
     * @param integer $row
     * @return integer
     */
    function rowCount($row)
    {
        $row = $this->cleanRowIdx($row);
        return count($this->buttonList[$row]);
    }
    
    /**
     *
     * @param type $buttonList
     * @param type $row Numbered 0-3
     * @return Js_Mce
     */
    function setButtonRow($buttonList, $row = 0)
    {
        if (!is_array($buttonList)) {
            return;
        }
        $row = $this->cleanRowIdx($row);
        $this->buttonList[$row] = $buttonList;
        return $this;
    }
    
    /**
     * Add an init parameter to the script
     * The value parameter is expected to hold a Json parameter value
     * be sure to add quotes of it is ment to be a js string
     * 
     * @param string $name
     * @param string $value
     * @return Js_Mce
     */
    function addParam($name, $value)
    {
        $this->paramList[$name] = $value;
        return $this;
    }
    
    /**
     * Remove a parameter from the parameter list.
     *
     * @param string $name
     * @return Js_Mce
     */
    function removeParam($name)
    {
        if (isset ($this->paramList[$name])) {
            unset($this->paramList[$name]);
        }
        return $this;
    }
    
    
    
    /**
     * Check the bounds of the row index and correct if required
     * 
     * @param integer $row
     * @return integer
     */
    private function cleanRowIdx($row)
    {
        if ($row < 0) { $row = 0; }
        if ($row > 3) { $row = 3; }
        return $row;
    }
    
    /**
     * Clean the position index
     *
     * @param integer $row
     * @param integer $pos
     * @return integer 
     */
    private function cleanPosIdx($row, $pos)
    {
        if ($pos < 0) { $pos = 0; }
        if ($pos > count($this->buttonList[$row])) {
            $pos = count($this->buttonList[$row]);
        }
        return $pos;
    }
}

