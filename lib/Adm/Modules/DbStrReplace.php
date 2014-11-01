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
class Adm_Modules_DbStrReplace extends Com_Web_Component
{

    /**
     * init
     *
     */
    function init()
    {
        $db = Tk_Db_Factory::getDb();

        $form = Form::create('StrReplace');
        $form->addEvent(Form_Event_Query::create('query'));

        $list = $db->getTableList();
        $list = array_combine($list, $list);
        $form->addField(Form_Field_Select::create('table', $list)->prependOption('-- Select --', ''));
        $form->addField(Form_Field_Text::create('field'));
        $form->addField(Form_Field_Textarea::create('find'))->setHeight(70);
        $form->addField(Form_Field_Textarea::create('replace'))->setHeight(70);

        $this->setForm($form);

    }

    /**
     * Show
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $this->getParent()->getTemplate()->insertText('_contentTitle', 'DB String Replace');



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
        $db = Tk_Db_Factory::getDb();
        if (!$db->tableExists($this->getForm()->getFieldValue('table'))) {
            $this->getForm()->addFieldError('table', 'Invalid Table Name');
        }

        if (!$this->getForm()->getFieldValue('field')) {
            $this->getForm()->addFieldError('field', 'Invalid Field Value');
        }

        if (!$this->getForm()->getFieldValue('find')) {
            $this->getForm()->addFieldError('find', 'Invalid Find text');
        }

//        if (!$this->getForm()->getFieldValue('replace')) {
//            $this->getForm()->addFieldError('replace', 'Invalid Replace Text');
//        }

        if ($this->getForm()->hasErrors()) {
            return;
        }
        try {
            $sql = sprintf( 'UPDATE `%s` SET `%s` = REPLACE(`%s`,\'%s\',\'%s\')',
                    Tk_Db_MyDao::escapeString($this->getForm()->getFieldValue('table')), Tk_Db_MyDao::escapeString($this->getForm()->getFieldValue('field')),
                    Tk_Db_MyDao::escapeString($this->getForm()->getFieldValue('field')), Tk_Db_MyDao::escapeString($this->getForm()->getFieldValue('find')),
                    Tk_Db_MyDao::escapeString($this->getForm()->getFieldValue('replace')) );
            $db->query($sql);
            $this->setMessage('Query Executed Successfully: ' . $sql);
        } catch (Exception $e) {
            $this->addError('Query Error: ' . $e->getMessage());
            $this->getForm()->addError('Query Error: ' . $e->getMessage());
        }

        Tk_Request::requestUri()->redirect();
    }

}
