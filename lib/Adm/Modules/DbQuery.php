<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An admin content box. Put text and stats within these box's on the admin home page
 *
 * @package Com
 */
class Adm_Modules_DbQuery extends Com_Web_Component
{

    /**
     * init
     *
     */
    function init()
    {

        $form = Form::create('DbQuery', $this->listing);
        $form->addEvent(Form_Event_Query::create('query'));

        $form->addField(Form_Field_Textarea::create('query'))->setHeight(70);

        $this->setForm($form);

    }

    /**
     * Show
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $this->getParent()->getTemplate()->insertText('_contentTitle', 'DB Query');




    }

}

class Form_Event_Query extends Form_ButtonEvent
{

    static function create($name)
    {
        $obj = new self($name);
        return $obj;
    }

    function execute()
    {
        //update [table_name] set [field_name] = replace([field_name],'[string_to_find]','[string_to_replace]');





    }


}