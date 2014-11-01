<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The interface to allow actions and filters to be added to a table
 *
 * @package Com
 */
abstract class Com_Ui_Table_ActionInterface extends Com_Ui_Table_ExtraInterface
{
    
    /**
     * Get the form id so we can use the javascript setForm(id, event); function
     *
     * @return string $id
     */
    function getFormId()
    {
        return $this->table->getFormId();
    }
    
    /**
     * Setup any form fields for this acion
     *
     * @param Com_Form_Object $form
     * @return Com_Form_Object
     */
    function setFormFields(Com_Form_Object $form)
    {
        return $form;
    }
    
    /**
     * Get the filter HTML to insert into the Table
     *
     * @return string
     */
    abstract function getHtml();

}