<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A base component object.
 *
 *
 * @package Com
 * @deprecated No longer in use
 */
class Com_Ui_Copyright extends Com_Web_Component
{

    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * makeTemplate
     *
     * @return string
     */
    function __makeTemplate()
    {
        $xmlStr = '<?xml version="1.0"?>
        <div class="Com_Ui_Copyright">
          Copyright &#169; ' . date('Y') . '
          <a href="http://www.tropotek.com.au/" target="_blank" title="Tropotek">Tropotek</a>
        </div>';
        return Com_Web_Template::load($xmlStr);
    }

    /**
     * Show
     *
     */
    function show()
    {
        $template = $this->getTemplate();
        

    }

}