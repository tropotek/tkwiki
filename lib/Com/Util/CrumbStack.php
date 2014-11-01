<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * A Wdk config object.
 *
 * @package Com
 */
class Com_Util_CrumbStack extends Tk_Object
{
    const CRUMB_VAR = '_crumbs';
    
    
    
    /**
     * @var Com_Util_CrumbStack
     */
    protected static $instance = array();
    
    
    private $name = '';
    
    /**
     * @var array
     */
    private $crumbs = array();
    
    private $showBackBtn = true;
    
    
    
    
    
    /**
     * Sigleton, No instances can be created.
     * Use:
     *   Com_Util_CrumbStack::getInstance()
     */
    private function __construct($name = '_widgetCrumbs')
    {
        $this->name = $name;
    }
    
    /**
     * Get an instance of this object
     *
     * @return Com_Util_CrumbStack
     */
    static function getInstance($name = '_widgetCrumbs')
    {
        if (!array_key_exists($name, self::$instance)) {
            $session = Tk_Session::getInstance();
            if (Tk_Session::get($name) instanceof Com_Util_CrumbStack) {
                self::$instance[$name] = Tk_Session::get($name);
            } else {
                self::$instance[$name] = new Com_Util_CrumbStack($name);
                $session->setParameter($name, self::$instance[$name]);
            }
        }
        return self::$instance[$name];
    }
    
    /**
     * Initalise a component for use with the crumbsStack
     *
     * @param Com_Web_Component $com
     */
    function init(Com_Web_Component $com)
    {
        if (preg_match('/^\/(admin|agent\/)?index\.html$/', Tk_Request::getInstance()->getRequestUri()->getPath())) {
            $this->reset();
        }
        if ($com !== $com->getPage()) {
            return;
        }
        $this->trimUrl(Tk_Request::requestUri());
        $pageTemplate = $com->getPage()->getTemplate();
        if ($pageTemplate->keyExists('var', self::CRUMB_VAR)) {
            if ($this->size() > 0) {
                $pageTemplate->setChoice(self::CRUMB_VAR);
                $pageTemplate->replaceHTML(self::CRUMB_VAR, $this->getListHtml());
                $current = $this->getCurrent();
                if ($current) {
                    $com->getTemplate()->setAttr('_back', 'href', $current->toString());
                    $pageTemplate->setAttr('_back', 'href', $current->toString());
                }
            }
        }
    }
    
    /**
     * Put a url onto the bread crumb stack
     *
     * @param Tk_Type_Url $url
     * @param String $name
     */
    function putUrl(Tk_Type_Url $url, $name = '')
    {
        if ($name == null) { // get name from the file
            $name = basename($url->getPath());
            $pos = strrpos($name, '.');
            if ($pos) {
                $name = substr($name, $pos + 1);
                $name = trim(preg_replace('/[A-Z]/', ' $0', ucfirst($name)));
            }
        }
        if (array_key_exists($name, $this->crumbs)) {
            $newArr = array();
            foreach ($this->crumbs as $cName => $cUrl) {
                if ($cName == $name) {
                    break;
                }
                $newArr[$cName] = $cUrl;
            }
            $this->crumbs = $newArr;
        }
        $this->crumbs[$name] = $url;
    }
    
    /**
     * Return the crumb based on the name if it exists
     *
     * @param string $name
     * @return Tk_Type_Url
     */
    function getCrumb($name)
    {
        return $this->crumbs[$name];
    }
    
    /**
     * Get the topmost url
     *
     * @return Tk_Type_Url
     */
    function getCurrent()
    {
        $keys = array_keys($this->crumbs);
        if (isset($keys[count($keys) - 1])) {
            return $this->getCrumb($keys[count($keys) - 1]);
        }
    }
    
    /**
     * reset the crumb stack.
     *
     */
    function reset()
    {
        $this->crumbs = array();
    }
    
    /**
     * trim the stack back to the requested Url
     *
     */
    function trimUrl(Tk_Type_Url $url)
    {
        $newArr = array();
        foreach ($this->crumbs as $cName => $cUrl) {
            //if ($url->getBasename() == $cUrl->getBasename()) {
            if ($url->toString() == $cUrl->toString()) {
                break;
            }
            $newArr[$cName] = $cUrl;
        }
        $this->crumbs = $newArr;
    }
    
    /**
     * get the size of the crumbs array
     *
     * @return integer
     */
    function size()
    {
        return count($this->crumbs);
    }
    
    /**
     * Get an HTML unorderd list of the crumbs
     *
     * @return string
     */
    function getListHtml()
    {
        if ($this->size() == 0) {
            return '';
        }
        $html = '';
        $i = 0;
        foreach ($this->crumbs as $name => $url) {
            $sep = '';
            if ($i < count($this->crumbs) - 1) {
                $sep = ' <span>&#187;</span> ';
            }
            $html .= sprintf('<li><a href="%s" title="%s">%s</a>%s</li>%s', htmlentities($url->toString()), htmlentities($name), htmlentities($name), $sep, "\n");
            $i++;
        }
        $back = '';
        if ($this->showBackBtn) {
            $back = sprintf('<a href="%s" class="back i16-back" title="Back To Prevous Page"></a>', htmlentities($this->getCurrent()->toString()));
        }
        return $back . " <ul>\n" . $html . "</ul>\n";
    }
    
    /**
     * Use to toggle the back button url
     *
     * @param boolean $b
     */
    function enableBackButton($b)
    {
        $this->showBackBtn = $b;
    }
    
    /**
     * Get the hash of the current crumb trail
     *
     * @return string
     */
    function getHash()
    {
        $str = '';
        foreach ($this->crumbs as $name => $url) {
            $str .= $name;
        }
        return md5($str);
    }
}