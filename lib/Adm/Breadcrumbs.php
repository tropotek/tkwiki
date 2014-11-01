<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * A class to manage and render a breadcrumb list.
 * USe get Instance to create the instance ot the object you want
 * 
 * @package Ui
 */
class Adm_Breadcrumbs extends Tk_Object
{
    
    /**
     * @var Adm_Breadcrumbs
     */
    protected static $instance = array();
    
    /**
     * @var string
     */
    private $name = '';
    
    /**
     * @var array
     */
    private $crumbs = array();
    
    
    
    /**
     * Sigleton, No instances can be created.
     * Use:
     *   Adm_Breadcrumbs::getInstance()
     * 
     * @param string $name
     */
    private function __construct($name)
    {
        $this->name = $name;
    }
    
    
    /**
     * Get an instance of this object
     *
     * @return Adm_Breadcrumbs
     */
    static function getInstance($name = 'defaultStack')
    {
        $sid = '_Breadcrumbs-' . $name;
        if (!array_key_exists($sid, self::$instance)) {
            $session = Tk_Session::getInstance();
            if (Tk_Session::exists($sid)) {
                self::$instance[$sid] = Tk_Session::get($sid);
            } else {
                self::$instance[$sid] = new self($name);
                $session->setParameter($sid, self::$instance[$sid]);
            }
        }
        return self::$instance[$sid];
    }
    
    /**
     * generate a name from the url basename
     * 
     * @param Tk_Type_Url $url 
     */
    static function createName(Tk_Type_Url $url)
    {
        $name = $url->getBasename();
        $pos = strrpos($name, '.');
        if ($pos) {
            $name = substr($name, 0, $pos);
            $name = trim(preg_replace('/[A-Z]/', ' $0', ucfirst($name)));
            if ($name == 'Index') {
                $name = 'Home';
            }
        }
        return $name;
    }
    
    /**
     * Put a url onto the top bread crumb stack
     *
     * @param Tk_Type_Url $url
     * @param String $name
     * @return Adm_Breadcrumbs
     */
    function add(Tk_Type_Url $url, $name = '')
    {
        if ($url->exists('_ajx') || $url->get('nc') == 'nc' || preg_match('|/ajax/|', $url->getPath())) {
            return;
        }
        if ($name == null) {
            $name = self::createName($url);
        }
        if (array_key_exists($name, $this->crumbs)) {
            $this->trimName($name);
        }
        $this->crumbs[$name] = $url;
        return $this;
    }
    
    /**
     * Remove a crumb by its name
     *
     * @param string $name
     * @return Adm_Breadcrumbs
     */
    function delete($name) 
    {
        if ($this->exists($name)) {
            unset($this->crumbs[$name]);
        }
        return $this;
    }
    
    /**
     * Return the crumb based on the name if it exists
     * Tip: If no name is supplied the topmost active crumb is returned
     * @param string $name
     * @return Tk_Type_Url
     */
    function get($name = null)
    {
        if ($name === null && count($this->crumbs) > 1) {
            $keys = array_keys($this->crumbs);
            $k = $keys[count($keys)-2];
            return $this->crumbs[$k]; // Return topmost crumb
        }
        if (array_key_exists($name, $this->crumbs)) {
            return $this->crumbs[$name];
        }
    }
    
    /**
     * Check if a crumb exits
     * 
     * @param string $name
     * @return boolean
     */
    function exists($name)
    {
        return array_key_exists($name, $this->crumbs);
    }
    
    /**
     * reset the crumb stack.
     *
     * @return Adm_Breadcrumbs
     */
    function reset()
    {
        $this->crumbs = array();
        return $this;
    }
    
    /**
     * get the size of the crumbs array
     *
     * @return integer
     */
    function count()
    {
        return count($this->crumbs);
    }
    
    /**
     * trim the stack back to the requested Url name
     * 
     * @param string $name
     * @return Adm_Breadcrumbs
     */
    function trimName($name)
    {
        $newArr = array();
        foreach ($this->crumbs as $cName => $cUrl) {
            if ($cName == $name) {
                break;
            }
            $newArr[$cName] = $cUrl;
        }
        $this->crumbs = $newArr;
        return $this;
    }
    
    /**
     * trim the stack back to the requested Url
     * 
     * @param Tk_Type_Url $url
     * @param boolean $ignorQueryyString
     * @return Adm_Breadcrumbs
     */
    function trimUrl(Tk_Type_Url $url, $ignorQueryString = false)
    {
        $newArr = array();
        foreach ($this->crumbs as $cName => $cUrl) {
            if ($ignorQueryString && $url->getBasename() == $cUrl->getBasename()) {
                break;
            }
            if (!$ignorQueryString && $url->toString() == $cUrl->toString()) {
                break;
            }
            $newArr[$cName] = $cUrl;
        }
        $this->crumbs = $newArr;
        return $this;
    }
    
    /**
     * Get an HTML unorderd list of the crumbs
     *
     * @return string
     */
    function getListHtml($showBackBtn = true)
    {
        if (!$this->count()) {
            return '';
        }
        $html = '';
        $i = 0;
        foreach ($this->crumbs as $name => $url) {
            $class = '';
            if ($i == count($this->crumbs) - 1) {
                $class = "last";
            }
            $html .= sprintf('<li class="%s"><a href="%s" title="%s">%s</a>%s</li>', $class, htmlentities($url->toString()), htmlentities($name), htmlentities($name), "\n");
            $i++;
        }
        $back = '';
        if ($showBackBtn && $this->get()) {
            $back = sprintf('<a href="%s" class="back i16-back" title="Back To Prevous Page"></a>', htmlentities($this->get()->toString()));
        }
        return $back . " <ul class=\"crumbs\">\n" . $html . "</ul>\n";
    }
    
    
}