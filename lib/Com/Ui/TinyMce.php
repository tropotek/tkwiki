<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * This script is to setup the TinyMCE javascript WYSIWYG Editor
 *
 * @package Com
 * @deprecated Use the Js/Ui/TinyMce.php object in the JdkLib package
 */
class Com_Ui_TinyMce extends Dom_Renderer
{

    const MODE_SIMPLE = 0;
    const MODE_BASIC = 1;
    const MODE_NORMAL = 2;
    const MODE_ADVANCED = 3;

    /**
     * @var integer
     */
    private $mode = self::MODE_SIMPLE;

    /**
     * @var string
     */
    private $initStr = '';

    /**
     * @var string
     */
    private $selectorClass = 'mceEditor';

    /**
     * @var string
     */
    private $deselectorClass = 'mceNoEditor';

    /**
     * @var array
     */
    private $initParams = array();

    /**
     * @var array
     */
    private $plugins = array();

    /**
     * @var Tk_Type_Url
     */
    private $editorCss = null;

    /**
     * @var array
     */
    private $settings = array();

    /**
     * A relative path form the site data path
     * @var string
     */
    private $libPath = '/tiny_mce3';

    /**
     * A relative path from the site data path
     * @var string
     */
    private $publicFilePath = '';

    /**
     * The elements to attach the editor to, options are:
     *   - `textareas`
     *   - `exact`
     *   - `none`
     *
     * @var string
     */
    private $mceMode = 'textareas';

    /**
     * __construct
     *
     */
    function __construct($mode = self::MODE_SIMPLE)
    {
        $this->publicFilePath = Com_Config::getDataPath() . '/.tinyMce';
        $this->libPath = Com_Config::getDataPath() . '/tiny_mce3';
        $this->mode = $mode;
    }

    /**
     * The current default init.
     *
     * @todo this will change in the future, and subclassing will be the preferred usage
     */
    function init()
    {
        $this->settings['FileRoot'] = Com_Config::getSitePath();
        $this->settings['HtdocRoot'] = Com_Config::getHtdocRoot();
        $this->settings['FileManagerPath'] = $this->publicFilePath;
        Tk_Session::set('mce-params', $this->settings);

        // Unzip tiny mce package if required.
        if (!is_dir($this->libPath) && is_writable(dirname($this->libPath))) {
            $shell = new Tk_Util_Exec();
            $cmd = sprintf("cd %s && tar zxf %s/lib/Jdk/other/tiny_mce3.tgz", dirname($this->libPath), $config->getSitePath());
            try {
                $shell->exec($cmd);
            } catch (Exception $e) {
                throw new Tk_ExceptionRuntime('If you are using a windows based system you will need to unzip TinyMCE manually to `/data/tiny_mce3`.');
            }
        }

        // add default jdkmanager plugin
        if ($this->mode >= 1) {
            $this->plugins = array_merge(array('jdkmanager' => 'jdkmanager', 'preelementfix' => 'preelementfix'), $this->plugins);
            if (!is_dir($this->publicFilePath)) {
                mkdir($this->publicFilePath, 0777, true);
            }
        }
        $this->addInitParam('remove_linebreaks', 'false');
        $this->addInitParam('convert_urls', 'false');
        $this->addInitParam('relative_urls', 'false');
        $this->addInitParam('remove_script_host', 'true');
        $sName = Tk_Session::getInstance()->getName();
        $this->addInitParam('dk_sessionName', enquote($sName));
        if ($this->editorCss != null) {
            $this->addInitParam('content_css', enquote($this->editorCss->toString()));
        }
        $paramStr = '';
        $i = 0;
        foreach ($this->initParams as $k => $v) {
            $paramStr .= "$k : $v";
            if ($i < count($this->initParams) - 1) {
                $paramStr .= ",\n";
            }
            $i++;
        }

        // build plugin strings
        $plugins = '';
        $extPlugins = '';
        if (count($this->plugins) > 0) {
            $plugins = implode(',', $this->plugins) . ',';
            $extPlugins = implode(',', $this->plugins) . ',|,';
        }

        // Replace all template strings
        $this->initStr = $this->getInit();
        $this->initStr = str_replace('@CSS_SELECTOR_CLASS@', $this->selectorClass, $this->initStr);
        $this->initStr = str_replace('@CSS_DESELECTOR_CLASS@', $this->deselectorClass, $this->initStr);
        $this->initStr = str_replace('@PLUGINS@', $plugins, $this->initStr);
        $this->initStr = str_replace('@EXT_PLUGINS@', $extPlugins, $this->initStr);
        $this->initStr = str_replace('@MCE_PARAMS@', $paramStr, $this->initStr);

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

        $path = str_replace(Com_Config::getSitePath(), '', $this->libPath);

        $jsUrl = new Tk_Type_Url($path . '/tiny_mce.js');
        $template->appendJsUrl($jsUrl->toString());
        $template->appendJs($this->initStr);

    }

    /**
     * Set the deselector class for tinymce params
     * (default: mceEditor)
     *
     * @param string $str
     */
    function setSelectorClass($str)
    {
        $this->selectorClass = $str;
    }

    /**
     * set the deselector class for tinymce
     * (default: mceNoEditor)
     *
     * @param string $str
     */
    function setDeselectorClass($str)
    {
        $this->deselectorClass = $str;
    }

    /**
     * Enable a plugin
     *
     * @param unknown_type $name
     */
    function enablePlugin($name)
    {
        $this->plugins[$name] = $name;
    }

    /**
     * Enable a plugin
     *
     * @param string $name
     */
    function disablePlugin($name)
    {
        $tmp = $this->plugins[$name];
        if (isset($this->plugins[$name])) {
            unset($this->plugins[$name]);
        }
        return $tmp;
    }

    /**
     * Add a MCE parameter to replace the @MCE_PARAMS@ string in the init templates
     *
     * For string `$value` parameters use enquote() to wrap the string in single quotes.
     *
     * @param string $name
     * @param string $value
     */
    function addInitParam($name, $value)
    {
        $this->initParams[$name] = $value;
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
     */
    function setEditorCss($url)
    {
        $this->editorCss = $url;
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
     *  o @PLUGINS@ Any plugins that are to be added, using Jdk_Ui_TinyMce::enablePlugin()
     *  o @Ext_PLUGINS@ Any plugins that are to be added, using Jdk_Ui_TinyMce::enablePlugin()
     *  o @MCE_PARAMS@ Any mce setup parameters, from Jdk_Ui_TinyMce::getInitParams()
     *
     * @param string $str
     * @see http://tinymce.moxiecode.com/
     * @see Jdk_Ui_TinyMce::getDefinedInit() For default examples
     */
    function setInit($str)
    {
        $this->initStr = $str;
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
     */
    function setDefaultMode($const)
    {
        $this->mode = $const;
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
     * Set the mce editors public path for adding user files.
     * Usually used by the jdkmanager plugin
     *
     * This should be the full file path to a writable directory
     *
     * @param string $str
     */
    function setPublicFilePath($str)
    {
        $this->publicFilePath = $str;
    }

    /**
     * Set the mce init mode
     * Default: 'textarea'
     *
     * @param string $mceMode
     */
    function setMceMode($mceMode)
    {
        $this->mceMode = $mceMode;
    }

    /**
     * Get the init string of a predefined mode.
     *
     * NOTE: USe the following to enable iframes:
     *     extended_valid_elements : 'iframe[align<bottom?left?middle?right?top|class|frameborder|height|id|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style|title|width]',
     *
     * @param integer $mode
     */
    private function getDefinedInit()
    {
        $mode = $this->mceMode;
        $str = "tinyMCE.init(";
        switch ($this->mode) {
            case self::MODE_ADVANCED :
                $str .= "{
  mode : '$mode',
  theme : 'advanced',
  skin : 'o2k7',
  editor_selector : '@CSS_SELECTOR_CLASS@',
  editor_deselector : '@CSS_DESELECTOR_CLASS@',
  plugins : '@PLUGINS@inlinepopups,safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template',
  theme_advanced_buttons1 : '@EXT_PLUGINS@bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect,|,forecolor,backcolor',
  theme_advanced_buttons2 : 'pastetext,pasteword,|,search,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,media,cleanup,removeformat,code,|,insertdate,inserttime,|,charmap,emotions,advhr',
  theme_advanced_buttons3 : 'tablecontrols,|,removeformat,visualaid,|,sub,sup,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking,|,fullscreen,|,help',
  theme_advanced_toolbar_location : 'top',
  theme_advanced_toolbar_align : 'left',
  theme_advanced_statusbar_location : 'bottom',
  extended_valid_elements : 'iframe[align<bottom?left?middle?right?top|class|frameborder|height|id|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style|title|width]',
  theme_advanced_resizing : true,
  @MCE_PARAMS@
}";
                break;
            case self::MODE_NORMAL :
                $str .= "{
  mode : '$mode',
  theme : 'advanced',
  skin : 'o2k7',
  editor_selector : '@CSS_SELECTOR_CLASS@',
  editor_deselector : '@CSS_DESELECTOR_CLASS@',
  plugins : '@PLUGINS@inlinepopups,safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template',
  theme_advanced_buttons1 : '@EXT_PLUGINS@bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect,|,forecolor,backcolor',
  theme_advanced_buttons2 : 'pastetext,pasteword,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,media,cleanup,removeformat,code,|,insertdate,inserttime,preview,|,hr,charmap,|,fullscreen,|,help',
  theme_advanced_buttons3 : '',
  theme_advanced_buttons4 : '',
  theme_advanced_toolbar_location : 'top',
  theme_advanced_toolbar_align : 'left',
  theme_advanced_statusbar_location : 'bottom',
  extended_valid_elements : 'iframe[align<bottom?left?middle?right?top|class|frameborder|height|id|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style|title|width]',
  theme_advanced_resizing : true,
  @MCE_PARAMS@
}";
                break;
            case self::MODE_BASIC :
                $str .= "{
  mode : '$mode',
  theme : 'advanced',
  skin : 'o2k7',
  editor_selector : '@CSS_SELECTOR_CLASS@',
  editor_deselector : '@CSS_DESELECTOR_CLASS@',
  plugins : '@PLUGINS@inlinepopups,safari,paste',
  theme_advanced_buttons1 : '@EXT_PLUGINS@bold,italic,underline,strikethrough,|,pastetext,pasteword,selectall,|,help',
  theme_advanced_buttons2 : '',
  theme_advanced_buttons3 : '',
  theme_advanced_buttons4 : '',
  theme_advanced_toolbar_location : 'top',
  theme_advanced_toolbar_align : 'left',
  //theme_advanced_resizing : true,
  @MCE_PARAMS@
}";
                break;
            case self::MODE_SIMPLE :
                $str .= "{
  mode : '$mode',
  theme : 'advanced',
  skin : 'o2k7',
  editor_selector : '@CSS_SELECTOR_CLASS@',
  editor_deselector : '@CSS_DESELECTOR_CLASS@',
  plugins : '@PLUGINS@inlinepopups,safari,paste',
  theme_advanced_buttons1 : '@EXT_PLUGINS@bold,italic,underline,|,pastetext,pasteword,|,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,forecolor,backcolors,|,link,unlink,image,code,|,help',
  theme_advanced_buttons2 : '',
  theme_advanced_buttons3 : '',
  theme_advanced_toolbar_location : 'top',
  theme_advanced_toolbar_align : 'left',
  theme_advanced_statusbar_location : 'bottom',
  //theme_advanced_resizing : true,
  @MCE_PARAMS@
}";
                break;
            default :
                $str .= "{
  @MCE_PARAMS@
}";
        }
        return $str . " ); \n";
    }

}