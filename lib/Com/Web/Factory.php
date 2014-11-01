<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * All widgets can be created from this object
 *
 * @package Com
 */
class Com_Web_Factory extends Tk_Object
{
    
    /**
     * @var Com_Web_Factory
     */
    protected static $instance = null;
    
    /**
     * This is a constructor
     * If no request session or response parameters given the default Sdk objects are used.
     *
     */
    protected function __construct()
    {
    }
    
    /**
     * Get an instance of the object factory
     *
     * @return Com_Web_Factory
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Com_Web_Factory();
        }
        return self::$instance;
    }
    
    /**
     * Create a page/owner component
     *
     * @param Tk_Type_Path $path
     * @return Com_Web_Component
     */
    function createPage(Tk_Type_Path $path)
    {
        $page = new Com_Web_Component();
        $page->setTemplate(Com_Web_Template::loadFile($path->getPath()));
        $this->createComponents($page);
        $page->setParent($page);
        return $page;
    }
    
    /**
     * Create the page component list
     *
     * @param Com_Web_Component $parent
     */
    function createComponents(Com_Web_Component $parent)
    {
        $componentList = $parent->getTemplate()->getComponentList();
        /* @var $metaData Com_Web_MetaData */
        foreach ($componentList as $metaData) {
            $com = null;
            $method = 'create' . $metaData->getClassname();
            if (method_exists($this, $method)) {
                $com = $this->$method($metaData);
            } else {
                $com = $this->createDefaultComponent($metaData);
            }
            
            if ($com == null) {
                continue;
            }
            
            Tk::log('Creating Component: ' . $metaData->getClassname(), Tk::LOG_INFO);
            $template = $this->getDefaultTemplate($metaData);
            if ($template) {
                $com->setTemplate($template);
            }
            $parent->addChild($com, $metaData->getInsertVar());
            if ($com->getTemplate()) {
                $this->createComponents($com);
            }
            foreach ($metaData->getParameters() as $k => $v) {
                $method = 'set' . ucfirst($k);
                if (method_exists($com, $method)) {
                    $com->$method($v);
                }
            }
        }
    }
    
    /**
     * The default component factory method.
     *
     * This method can be overriden in sub classes, or a custom factory method
     * for a specific component can be added. The custom factory method must
     * accept the same parameters as the default method and the name must
     * follow the format of the classname prefixed with the string 'create',
     * for example:
     *
     * createDk_Modules_ImageGallery_Manager(Com_Web_MetaData $metaData)
     *
     * @param Com_Web_MetaData $metaData
     * @return Com_Web_Component
     */
    function createDefaultComponent(Com_Web_MetaData $metaData)
    {
        $class = $metaData->getClassname();
        if (!class_exists($class)) {
            return;
        }
        $obj = new $class();
        return $obj;
    }
    
    /**
     * Create a default component template
     *
     * @param string $classname
     * @return Com_Web_Template  Returns null if no tempalte exists
     */
    function getDefaultTemplate(Com_Web_MetaData $metaData)
    {
        $template = null;
        // Check for an inline template
        if ($metaData->getInlineDom()) {
            return new Com_Web_Template($metaData->getInlineDom());
        }
        
        // Check for an external template
        $arr = explode('_', $metaData->getClassname());
        $n = count($arr);
        $src = $arr[$n - 1];
        
        $language = 'en_GB';
        if (Com_Config::getLanguage()) {
            $language = Com_Config::getLanguage();
        }
        array_pop($arr);
        $templateFile = implode('/', $arr);
        
        // Places to search for templates
        $tplFiles = array();
        $tplFiles[] = 'templates/language/' . $language . '/' . $metaData->getClassname() . '.xml';
        $tplFiles[] = 'templates/' . $metaData->getClassname() . '.xml';
        $tplFiles[] = $templateFile . '/language/' . $language . '/' . $src . '.xml';
        $tplFiles[] = $templateFile . '/' . $src . '.xml';
        
        //vd($tplFiles);
        foreach ($tplFiles as $templateFile) {
            $file = Com_Config::getSitePath() . '/lib/' . $templateFile;
            if (is_file($file)) {
                $template = Com_Web_Template::loadFile($file);
                break;
            }
        }
        return $template;
    }

}