<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An admin content box. Put text and stats within these box's on the admin home page
 *
 * @package Adm
 */
abstract class Adm_Ui_Box extends Com_Web_Component
{
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = '<?xml version="1.0"?>
<div class="cBox">
  <div class="head" var="title"></div>
  <div id="cbox" var="boxId">
    <div class="boxContent" var="content"></div>
    <div class="foot">
      &#160;<a href="#" class="config" var="footUrl" choice="footUrl"></a>
    </div>
  </div>
</div>
        ';
        $template = Com_Web_Template::load($xmlStr);
        return $template;
    }
    
    /**
     * execute
     */
    function execute()
    {
        $template = $this->getTemplate();
        $template->setAttr('title', 'id', "trigger_acbox" . $this->getId());
        $template->setAttr('boxId', 'id', 'acbox' . $this->getId());
        $template->appendJs("$(document).ready(function() { registerSlideBox('acbox{$this->getId()}'); });");
        
        parent::execute();
    }
    
}