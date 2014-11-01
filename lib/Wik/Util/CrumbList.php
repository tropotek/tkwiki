<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Tropotek Development
 */

/**
 *
 *
 * @package Util
 */
class Wik_Util_CrumbList extends Wik_Web_Component
{

    const SID = '__CrumbList';

    /**
     * @var Wik_Util_CrumbList
     */
    protected static $instance = null;

    /**
     * @var array
     */
    private $list = array();

    /**
     * @var integer
     */
    private $maxCrumbs = 5;

    /**
     * __construct
     *
     * @param integer $max
     */
    function __construct($max = 5)
    {
        parent::__construct();
        $this->max = intval($max);
    }

    /**
     * Get an instance of this object
     *
     * @return Wik_Util_CrumbList
     */
    static function getInstance($max = 5)
    {
        if (self::$instance == null) {
            if (Tk_Session::getInstance()->exists(self::SID)) {
                self::$instance = Tk_Session::getInstance()->getParameter(self::SID);
            } else {
                self::$instance = new Wik_Util_CrumbList($max);
                Tk_Session::getInstance()->setParameter(self::SID, self::$instance);
            }
        }
        self::$instance->setTemplate(self::$instance->__makeTemplate());
        // Reset the template
        return self::$instance;
    }

    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = '<?xml version="1.0"?>
<ul class="menu Wik_Util_CrumbList" choice="Wik_Util_CrumbList">
  <li repeat="row" var="row"><a href="javascript:;" var="url"></a> <span choice="sep">&#187;</span> </li>
</ul>';
        $template = Com_Web_Template::load($xmlStr);
        return $template;
    }

    /**
     * Get the render html for the crumbs list
     *
     * @param Dom_Template $template
     * @return string
     */
    function show()
    {
        $template = $this->getTemplate();

        if (count($this->list) <= 0) {
            return;
        }

        $template->setChoice('Wik_Util_CrumbList');
        $i = 0;
        /* @var $url Tk_Type_Url */
        foreach ($this->list as $name => $url) {
            $repeat = $template->getRepeat('row');
            $repeat->setAttr('url', 'href', $url->toString());
            $repeat->insertText('url', $name);
            if ($i < (count($this->list) - 1)) {
                $repeat->setChoice('sep');
            }
            $repeat->appendRepeat();
            $i++;
        }
    }

    /**
     * Add a url to the crumb list.
     * This will drop urls when the size of the array is greater than $max
     *
     * @param string $name
     * @param Tk_Type_Url $url
     */
    function addUrl($name, Tk_Type_Url $url)
    {
        if ($name == null) {
            return;
        }
        if (array_key_exists($name, $this->list)) {
            unset($this->list[$name]);
        }
        if (count($this->list) >= $this->max) {
            array_shift($this->list);
        }
        $this->list[$name] = $url;
    }

    /**
     * Clear the crumb list
     *
     */
    function reset()
    {
        $this->list = array();
    }

}