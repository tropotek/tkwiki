<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Render an array of Tk objects to a table
 *
 * @package Adm
 */
class Adm_Table extends Com_Ui_Table_Base
{
    
    const MSG_CLASS_ERROR = 'errorBox';
    const MSG_CLASS_WARNING = 'warningBox';
    const MSG_CLASS_NOTICE = 'noticeBox';
    
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        if ($this->hasTemplate()) {
            return;
        }
        
        /* NOTE: The form here must use a GET request to avoid the
         * reload message of the browser when a POST request is performed
         *
         * ADDITIONAL: Changed to POST, all admin back buttons must not use browser/javascript back/history
         * This is so we can use the get string for other data
         */
        $xmlStr = sprintf('<?xml version="1.0"?>
<div class="Adm_Table">
  <form id="%s" method="post" var="formId">
    <div class="message" var="message" choice="message"></div>
    <div class="actions" choice="actions">
      <div class="actionRow">
        <div class="left" choice="action">
          <span var="actionCell" repeat="actionCell"></span>
          %s
        </div>
      </div>
      <div class="filterRow" id="filterRow" choice="filter">
        <div var="filterCell" repeat="filterCell"></div>
        %s
      </div>
      <div class="clear"></div>
    </div>
    
    <div class="pager">
      <table border="0" cellpadding="0" cellspacing="0"><tr>
        <td class="r">
          <div var="Com_Ui_Results" />
        </td>
        <td class="p">
          <div var="Com_Ui_Pager" />
        </td>
        <td class="l">
          <div var="Com_Ui_Limit" />
        </td>
      </tr></table>
    </div>
    
    <table border="0" cellpadding="0" cellspacing="0" class="manager" var="manager">
      <thead>
        <tr>
          <th var="th" repeat="th"></th>
        </tr>
      </thead>
      <tbody>
        <tr var="tr" repeat="tr">
          <td var="td" repeat="td"></td>
        </tr>
      </tbody>
    </table>
    
    <div class="pager">
      <table border="0" cellpadding="0" cellspacing="0"><tr>
        <td class="r">
          <div var="Com_Ui_Results" />
        </td>
        <td class="p">
          <div var="Com_Ui_Pager" />
        </td>
        <td class="l">
          <div var="Com_Ui_Limit" />
        </td>
      </tr></table>
    </div>
    
  </form>
  <div class="clear" />
</div>
', $this->getFormId(), $this->getActionHtml(), $this->getFilterHtml());
        
        $template = Com_Web_Template::load($xmlStr);
        
        // Setup form
        $form = new Com_Form_Object($this->getFormId());
        $this->setForm($form);
        
        /* @var $action Com_Ui_Table_Action */
        foreach ($this->actions as $action) {
            $action->setFormFields($form);
        }
        /* @var $action Com_Ui_Table_Action */
        foreach ($this->filters as $action) {
            $action->setFormFields($form);
        }
        
        return $template;
    }
    
    
}