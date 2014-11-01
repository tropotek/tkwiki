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
class Js_Ui_TinyMce extends Dom_Renderer
{

    const MODE_SIMPLE = 0;
    const MODE_BASIC = 1;
    const MODE_NORMAL = 2;
    const MODE_ADVANCED = 3;
    const MODE_FULL = 4;

    /**
     * @var integer
     */
    private $mode = self::MODE_SIMPLE;

    /**
     * @var string
     */
    private $initStr = '';

    /**
     * @var array
     */
    private $initParams = array();

    /**
     * @var array
     */
    private $plugins = array();

    /**
     * @var array
     */
    private $buttons = array();

    /**
     * @var Tk_Type_Url
     */
    protected $editorCss = null;

    /**
     * @var string
     */
    protected $fileManagerPath = '';

    /**
     * @var string
     */
    protected $mcePath = '';

    /**
     * @var string
     */
    protected $mceUrl = '';

    /**
     * The elements to attach the editor to, options are:
     *   - textareas
     *   - exact
     *   - none
     * @var string
     */
    protected $mceMode = 'textareas';

    /**
     * @var string
     */
    protected $cssSelector = 'mceEditor';

    /**
     * @var string
     */
    protected $cssDeselector = 'mceNoEditor';


    /**
     * __construct
     *
     * @params integer $mode
     */
    function __construct($mode = self::MODE_SIMPLE)
    {
        $this->mode = $mode;
        $this->plugins = array('jdkmanager' => 'jdkmanager');

        $this->fileManagerPath = Tk_Config::getDataPath() . '/fileManager';
        $this->mcePath = Tk_Config::getDataPath() . '/tiny_mce3';
        $this->mceUrl = Tk_Config::getDataUrl() . '/tiny_mce3';
    }


    /**
     * The current default init.
     *
     */
    function init()
    {
        $config = Com_Config::getInstance();

        // Unzip tiny mce package if required.
        if (!is_dir($this->mcePath) && is_writable(dirname($this->mcePath))) {
            $shell = new Tk_Util_Exec();
            $cmd = sprintf("cd %s && tar zxf %s/lib/Js/tinymce/tiny_mce3.tgz", dirname($this->mcePath), Tk_Config::getSitePath());
            try {
                $shell->exec($cmd);
            } catch (Exception $e) {
                throw new Tk_ExceptionRuntime('Please manually unzip TinyMCE ([sitePath]/lib/Js/tinymce/tiny_mce3.tgz) manually to `' . $this->mcePath . '`.');
            }
        }

        $this->addInitParam('remove_linebreaks', 'false');
        $this->addInitParam('convert_urls', 'false');
        $this->addInitParam('relative_urls', 'false');
        $this->addInitParam('remove_script_host', 'true');
        $this->addInitParam('save_onsavecallback', 'function() { $(window).unbind(\'beforeunload\'); submitForm(document.forms[0], \'save\'); }');
        $this->addInitParam('save_oncancelcallback', 'function() { $(window).unbind(\'beforeunload\'); submitForm(document.forms[0], \'cancel\'); }');

        if ($this->editorCss != null) {
            $this->addInitParam('content_css', enquote($this->editorCss->toString()));
        }
        $paramStr = '';
        foreach ($this->initParams as $k => $v) {
            $paramStr .= "  $k : $v, \n";
        }
        if ($paramStr) {
            $paramStr = substr($paramStr, 0, -3);
        }

        // build plugin strings
        $plugins = '';
        $extPlugins = '';
        if (count($this->buttons) > 0) {
            $extPlugins .= implode(',', $this->buttons) . ',|,';
        }
        if (count($this->plugins) > 0) {
            $plugins = implode(',', $this->plugins) . ',';
            $extPlugins .= implode(',', $this->plugins) . ',|,';
        }
        $plugins =  'preelementfix,' . $plugins;

        // Replace all template strings
        $this->initStr = $this->getInit();
        $this->initStr = str_replace('@MODE@', $this->mceMode, $this->initStr);
        $this->initStr = str_replace('@CSS_SELECTOR_CLASS@', $this->cssSelector, $this->initStr);
        $this->initStr = str_replace('@CSS_DESELECTOR_CLASS@', $this->cssDeselector, $this->initStr);
        $this->initStr = str_replace('@PLUGINS@', $plugins, $this->initStr);
        $this->initStr = str_replace('@EXT_PLUGINS@', $extPlugins, $this->initStr);
        $this->initStr = str_replace('@MCE_PARAMS@', $paramStr, $this->initStr);

        $this->initStr = "tinyMCE.init(\n" . $this->initStr . ");";
    }

    /**
     * show
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $this->init();

        Tk_Session::set('js.tinymce.fileManagerPath', $this->fileManagerPath);
        Tk_Session::set('js.tinymce.mcePath', $this->mcePath);
        $jsUrl = new Tk_Type_Url($this->mceUrl . '/tiny_mce.js');
        $template->appendJsUrl($jsUrl->toString());
        $template->appendJs($this->initStr);
    }

    /**
     * Enable a plugin
     *
     * @param string $name
     * @return Js_Ui_TinyMce
     */
    function enablePlugin($name)
    {
        $this->plugins[$name] = $name;
        return $this;
    }

    /**
     * Enable a plugin
     *
     * @param string $name
     * @return Js_Ui_TinyMce
     */
    function disablePlugin($name)
    {
        if (isset($this->plugins[$name])) {
            unset($this->plugins[$name]);
        }
        return $this;
    }

    /**
     * Enable a button
     *
     * @param string $name
     * @return Js_Ui_TinyMce
     */
    function enableButton($name)
    {
        $this->buttons[$name] = $name;
        return $this;
    }

    /**
     * Enable a button
     *
     * @param string $name
     * @return Js_Ui_TinyMce
     */
    function disableButton($name)
    {
        if (isset($this->buttons[$name])) {
            unset($this->buttons[$name]);
        }
        return $this;
    }

    /**
     * Add a MCE parameter to replace the @MCE_PARAMS@ string in the init templates
     *
     * For string `$value` parameters use enquote() to wrap the string in single quotes.
     *
     * @param string $name
     * @param string $value
     * @return Js_Ui_TinyMce
     */
    function addInitParam($name, $value)
    {
        $this->initParams[$name] = $value;
        return $this;
    }

    /**
     * Get teh init params array
     *
     * @return array
     */
    function getInitParams()
    {
        return $this->initParams;
    }

    /**
     * Set the base css path for the editor window
     *
     *  content_css: <url>
     *
     * @param Tk_Type_Url $url
     * @return Js_Ui_TinyMce
     */
    function setEditorCss($url)
    {
        $this->editorCss = $url;
        return $this;
    }

    /**
     * Get the editor css files url
     *
     * @return Tk_Type_Url
     */
    function getEditorCss()
    {
        return $this->editorCss;
    }

    /**
     * Set the init string for the TinyMCE editor
     * Set the init string template, the replaceable variables are:
     *
     *  o @CSS_SELECTOR_CLASS@ The css selector class (default: mceEditor)
     *  o @CSS_DESELECTOR_CLASS@ The css deselector class (default: mceNoEditor)
     *  o @PLUGINS@ Any plugins that are to be added, using Js_Ui_TinyMce::enablePlugin()
     *  o @Ext_PLUGINS@ Any plugins that are to be added, using Js_Ui_TinyMce::enablePlugin()
     *  o @MCE_PARAMS@ Any mce setup parameters, from Js_Ui_TinyMce::getInitParams()
     *
     * @param string $str
     * @see http://tinymce.moxiecode.com/
     * @see Js_Ui_TinyMce::getDefinedInit() For default examples
     * @return Js_Ui_TinyMce
     */
    function setInit($str)
    {
        $this->initStr = $str;
        return $this;
    }

    /**
     * Return the mce javascript init template string
     *
     * @return string
     */
    final function getInit()
    {
        if ($this->initStr) {
            return $this->initStr;
        }
        return $this->getDefinedInit();
    }

    /**
     * Set the display Mode of the editor
     * (Default: self::MODE_SIMPLE)
     *
     * @param integer $const
     * @return Js_Ui_TinyMce
     */
    function setDefaultMode($const)
    {
        $this->mode = $const;
        return $this;
    }
    /**
     *
     * @param unknown_type $const
     * @return Js_Ui_TinyMce
     */
    function setMode($const)
    {
        $this->mode = $const;
        return $this;
    }

    /**
     * Get the current mode of MCE
     *
     * @return integer
     */
    function getDefaultMode()
    {
        return $this->mode;
    }

    /**
     *
     * @return number
     */
    function getMode()
    {
        return $this->mode;
    }


    /**
     * Set the class to enable the editor
     *
     * @param $str
     */
    function setCssSelector($str)
    {
        $this->cssSelector = $str;
    }

    /**
     * Set the class to disable the editor
     *
     * @param $str
     */
    function setCssDeselector($str)
    {
        $this->cssDeselector = $str;
    }

    /**
     * The elements to attach the editor to, options are:
     *   - `textareas`
     *   - `exact`
     *   - `none`
     *
     * @param $str
     */
    function setMceMode($str)
    {
        $this->mceMode = $str;
    }

    /**
     * Set the URL location of the mce files
     *
     * @param $str
     */
    function setMceUrl($str)
    {
        $this->mceUrl = $str;
    }

    /**
     * Set the path location of the mce files
     *
     * @param $str
     */
    function setMcePath($str)
    {
        $this->mcePath = $str;
    }

    /**
     * Set the file manager plugin path location.
     *
     * @param $str
     * @todo Find a better way to handle this.
     */
    function setFileManagerPath($str)
    {
        $this->fileManagerPath = $str;
    }

    /**
     * Get the init string of a predefined mode.
     *
     * NOTE: Use the following to enable iframes:
     *     extended_valid_elements : 'iframe[align<bottom?left?middle?right?top|class|frameborder|height|id|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style|title|width]',
     *
     * @param integer $mode
     */
    private function getDefinedInit()
    {
        $extend = '';
        $str = '';
        switch ($this->mode) {
            case self::MODE_FULL :
                $str = "{
    mode : '@MODE@',
    theme : 'advanced',
    skin : 'o2k7',
    editor_selector : '@CSS_SELECTOR_CLASS@',
    editor_deselector : '@CSS_DESELECTOR_CLASS@',
    plugins : '@PLUGINS@safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount',
    theme_advanced_buttons1 : '@EXT_PLUGINS@,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect',
    theme_advanced_buttons2 : 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor',
    theme_advanced_buttons3 : 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
    theme_advanced_buttons4 : 'insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak',
    theme_advanced_toolbar_location : 'top',
    theme_advanced_toolbar_align : 'left',
    theme_advanced_statusbar_location : 'bottom',
    theme_advanced_resizing : true,
    $extend
@MCE_PARAMS@
}";
                break;
            case self::MODE_ADVANCED :
                $str = "{
  mode : '@MODE@',
  theme : 'advanced',
  skin : 'o2k7',
  editor_selector : '@CSS_SELECTOR_CLASS@',
  editor_deselector : '@CSS_DESELECTOR_CLASS@',
  plugins : '@PLUGINS@inlinepopups,safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template',
  theme_advanced_buttons1 : '@EXT_PLUGINS@bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect,|,forecolor,backcolor',
  theme_advanced_buttons2 : 'pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,media,cleanup,removeformat,code,|,insertdate,inserttime,|,charmap,emotions,advhr',
  theme_advanced_buttons3 : 'tablecontrols,|,removeformat,visualaid,|,sub,sup,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking,|,fullscreen,|,help',
  theme_advanced_toolbar_location : 'top',
  theme_advanced_toolbar_align : 'left',
  theme_advanced_statusbar_location : 'bottom',
  $extend
  theme_advanced_resizing : true,
@MCE_PARAMS@
}";
                break;
            case self::MODE_NORMAL :
                $str = "{
  mode : '@MODE@',
  theme : 'advanced',
  skin : 'o2k7',
  editor_selector : '@CSS_SELECTOR_CLASS@',
  editor_deselector : '@CSS_DESELECTOR_CLASS@',
  plugins : '@PLUGINS@inlinepopups,safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template',
  theme_advanced_buttons1 : '@EXT_PLUGINS@bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect,|,forecolor,backcolor',
  theme_advanced_buttons2 : 'pasteword,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,media,image,cleanup,removeformat,code,|,insertdate,inserttime,preview,|,hr,charmap,|,fullscreen,|,help',
  theme_advanced_buttons3 : '',
  theme_advanced_buttons4 : '',
  theme_advanced_toolbar_location : 'top',
  theme_advanced_toolbar_align : 'left',
  theme_advanced_statusbar_location : 'bottom',
  $extend
  theme_advanced_resizing : true,
@MCE_PARAMS@
}";
                break;
            case self::MODE_BASIC :
                $str = "{
  mode : '@MODE@',
  theme : 'advanced',
  skin : 'o2k7',
  editor_selector : '@CSS_SELECTOR_CLASS@',
  editor_deselector : '@CSS_DESELECTOR_CLASS@',
  plugins : '@PLUGINS@inlinepopups,safari,paste,template',
  theme_advanced_buttons1 : '@EXT_PLUGINS@bold,italic,underline,|,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,link,unlink,|,image,cleanup',
  theme_advanced_buttons2 : '',
  theme_advanced_buttons3 : '',
  theme_advanced_toolbar_location : 'top',
  theme_advanced_toolbar_align : 'left',
  theme_advanced_statusbar_location : 'bottom',
  //theme_advanced_resizing : true,
@MCE_PARAMS@
}";
                break;
            case self::MODE_SIMPLE :
                $str = "{
  mode : '@MODE@',
  theme : 'advanced',
  skin : 'o2k7',
  editor_selector : '@CSS_SELECTOR_CLASS@',
  editor_deselector : '@CSS_DESELECTOR_CLASS@',
  plugins : '@PLUGINS@inlinepopups,safari,paste,template',
  theme_advanced_buttons1 : '@EXT_PLUGINS@bold,italic,underline,strikethrough',
  theme_advanced_buttons2 : '',
  theme_advanced_buttons3 : '',
  theme_advanced_buttons4 : '',
  theme_advanced_toolbar_location : 'top',
  theme_advanced_toolbar_align : 'left',
  //theme_advanced_resizing : true,
@MCE_PARAMS@
}";
                break;
            default :
                $str = "{
@MCE_PARAMS@
}\n";
        }
        return $str;
    }

}