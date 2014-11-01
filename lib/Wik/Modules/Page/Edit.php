<?php

/*
 * This file is part of the DkLib.
 *   You can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   You should have received a copy of the GNU Lesser General Public License
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A base component object.
 * NOTE: Only registered users can create/edit/delete pages.
 *
 * @package Modules
 */
class Wik_Modules_Page_Edit extends Wik_Web_Component
{
    /**
     * @var Wik_Db_Page
     */
    private $page = null;

    /**
     * @var Wik_Db_Text
     */
    private $text = null;


    /**
     * __construct
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->addEvent('save', 'doSubmit');
        $this->addEvent('ping', 'doPing');
        $this->addEvent('delete', 'doDelete');
        $this->addEvent('cancel', 'doCancel');

        $this->setAccessGroup(Wik_Db_User::GROUP_USER);
    }


    /**
     * The default init method
     *
     */
    function init()
    {
        if (!Tk_Request::exists('pageName')) {
            Tk_Request::set('pageName', 'Home');
        }
        $this->page = Wik_Db_PageLoader::findByName(Tk_Request::get('pageName'));

        // Create a new page
        if ($this->page == null) {
            $this->page = new Wik_Db_Page();
            $this->page->setUserId($this->getUser()->getId());
            //$this->page->setGroupId($this->getUser()->getGroupId());
            $this->page->setGroupId(1);
            $this->page->setTitle(str_replace('_', ' ', Tk_Request::get('pageName')));
            $this->page->setName(Tk_Request::get('pageName'));
        }
        if (!$this->page->canWrite($this->getUser())) {
            return;
        }

        $this->text = new Wik_Db_Text();
        $this->text->setUserId($this->getUser()->getId());
        if ($this->page->getCurrentTextId() > 0) {
            $oldText = Wik_Db_TextLoader::find($this->page->getCurrentTextId());
            if ($oldText) {
                $this->text->setText($oldText->getText());
            }
        }

        $url = new Tk_Type_Url('/index.html');
        if ($this->page->getId() > 0) {
            $url = $this->page->getPageUrl();
        }
        if (Tk_Request::get('pageName') == 'Menu') {
            $url = Tk_Type_Url::createUrl('/page/Home');
        }

        $form = Form::create('Edit', $this->page);
        $form->addEvent(Wik_Form_Event_Save::create('save'));
        $form->addEvent(Form_Event_Cancel::create($url));
        $form->addEvent(Wik_Form_Event_Lock::create());

        $form->addField(Form_Field_Text::create('title'))->setWidth(800);
        if ($this->getUser()->getGroupId() == Wik_Db_User::GROUP_ADMIN) {
            $list = Wik_Db_UserLoader::findActive();
            $form->addField(Form_Field_Select::create('userId', $list))->prependOption('-- Select --', 0)->setWidth(200);
        }
        if ($this->getUser()->getGroupId() == Wik_Db_User::GROUP_ADMIN || $this->page->getUserId() == $this->getUser()->getId()) {
            $list = array(array('-- Select --','0'), array('User','1'), array('Admin','128'));
            $form->addField(Form_Field_Select::create('groupId', $list))->setWidth(200);
            $form->addField(Form_Field_Checkbox::create('enableComment'))->setLabel('Comments');
            $form->addField(Form_Field_Text::create('permissions'))->setWidth(50);
        }
        $mce = $form->addField(Form_Field_Mce::create('text', Js_Mce::createFull()))->setWidth(800)->setHeight(600)->getMce();
        $form->addField(Form_Field_Textarea::create('keywords'));
        $form->addField(Form_Field_Textarea::create('css'));
        $form->addField(Form_Field_Textarea::create('javascript'));
        $form->addField(Form_Field_Hidden::create('dirty'))->setValue('0');

        $this->setForm($form);

        $mce->addButton('pretag');
        $mce->addPlugin(Wik_Mce_WikiFindPage::create());
        $mce->addPlugin(Wik_Mce_WikiCreatePage::create());
        $mce->addPlugin(Js_Mce_Plugin_FileManager::create());

        $mce->addButton('|', 0, 0);
        $mce->addButton('save', 0, 0);

        $mce->addParam('content_css', enquote(Tk_Type_Url::create('css/tinymce.css')->toString()));
        $mce->addParam('convert_urls', 'false');
        $mce->addParam('relative_urls', 'false');
        $mce->addParam('remove_script_host', 'true');
        $mce->addParam('save_enablewhendirty', 'true');
        $mce->addParam('fix_nesting', 'true');
        //$mce->addParam('onchange_callback', 'function() {  }');

        $icon = Tk_Type_Url::create('/images/code.gif')->toString();
        $mce->addParam('setup', "function(ed) {
    ed.onChange.add(function(ed, l) {
        $('#fid-dirty').val('1');
    });
    // Add a custom button
    ed.addButton('pretag', {
        title : 'Add the &lt;pre&gt; tag.',
        image : '$icon',
        onclick : function() {
            var txt = ed.selection.getContent({format : 'text'});
            if (txt == '') {
              txt = '&#160;';
            }
            // TODO: Place cursor at start of tag?
            ed.selection.setContent('<pre class=\"prettyprint\">' + txt + '</pre>');
            // TODO: if inside a pre tag step out and create a new p tag if a sibling
            //  one does not exist...
        }
    });
  }");

    }

    /**
     * The default event handler.
     *
     */
    function doDefault()
    {
        $this->getForm()->loadFromObject($this->text);
        $this->getForm()->loadFromObject($this->page);

        if (!$this->page->canWrite($this->getUser())) {
            return;
        }

        $uid = $this->getUser()->getId();
        if ($this->page && $this->page->getLock()) {
            if (!$this->page->getLock()->isEditable($uid)) {
                $url = $this->page->getPageUrl();
                $url->redirect();
            } else {
                $this->page->getLock()->lock($uid);
            }
        }
    }

    function doDelete()
    {
        $url = Tk_Type_Url::createUrl('/page/Home');
        $page = Wik_Db_PageLoader::find(Tk_Request::get('delete'));
        if ($page && $page->canDelete($this->getUser())) {
            $page->delete();
            $url = $page->getPageUrl();
        }
        if (Tk_Request::get('pageName') == 'Menu') {
            $url = Tk_Type_Url::createUrl('/page/Home');
        }
        $url->redirect();
    }


    /**
     * doPing
     *
     */
    function doPing()
    {
        $this->page->getLock()->lock($this->getUser()->getId());
        exit();
    }

    /**
     * The default show method.
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();

        if (!$this->getUser()) {
            $template->setChoice('noWrite');
            return;
        }

        $template->insertText('title', 'Editing: ' . $this->page->getTitle());

        $url = Tk_Type_Url::createUrl('/lib/Wik/js/jquery.chmod.js');
        $this->getTemplate()->appendJsUrl($url);

        if ($this->getUser()->getGroupId() == Wik_Db_User::GROUP_ADMIN) {
            $template->setChoice('admin');
        }
        if ($this->page->getId() > 0) {
            $template->setChoice('update');
        }
        if ($this->getUser()->getGroupId() == Wik_Db_User::GROUP_ADMIN || $this->page->getUserId() == $this->getUser()->getId()) {
            $template->setChoice('owner');
            // Add Chmod plugin
            $js = "
$(function(){
  $('#fid-permissions').chmod({access : ['delete', 'write', 'read']});
});
            ";
            $template->appendJs($js);
        }


        $css = <<<CSS
form.edit div.optional, form.edit div.required, form.edit div.field {
  clear: none;
  float: left;
  background-color: transparent;

}
form div.optional label, label.optional {
  display: block;
  float: left;
  clear: right;
  margin: 0;
  padding: 0;
  text-align: left;
}

form div input[type="text"], form div input.inputText, form div input.inputPassword,
form div select, form div textarea, input.inputCheckbox, input.inputRadio {
  display: block;
  float: left;
  clear: left;
}

.admFieldBox {
  display: inline-block;
  width: 100%;
}

div.fid-keywords {
  clear: left !important;
}

.eventTop,
.field.text label {
  display: none;
}
 form.edit div.field.title {
    width: 100%;
}
 form.edit div.field.text {
    width: 100%;
}
CSS;

        $template->appendCss($css);

    }

}

class Wik_Form_Event_Save extends Form_ButtonEvent
{

    /**
     * Create an instance of this object
     *
     * @param string $name
     * @param string $label
     * @return Form_Event_Save
     */
    static function create($name)
    {
        $obj = new self($name);
        return $obj;
    }

    /**
     * (non-PHPdoc)
     * @see Form_Event::execute()
     */
    function execute()
    {
        $page = $this->getForm()->getObject();

        $text = new Wik_Db_Text();
        $text->setUserId(Auth::getUser()->getId());

        $this->getForm()->loadObject($page);
        $this->getForm()->loadObject($text);
        $this->getForm()->addFieldErrors($page->getValidator()->getErrors());
        $this->getForm()->addFieldErrors($text->getValidator()->getErrors());

        if ($this->getForm()->hasErrors()) {
            return;
        }
        //$this->setMessage('Page Updated');

        $text->setPageId($page->getVolitileId());
        if ($this->getForm()->getFieldValue('dirty') == '1') {
            $text->save();
            $page->setSize($text->getSize());
            $page->setCurrentTextId($text->getId());
        }
        $page->save();

        $url = $page->getPageUrl();
        if (Tk_Request::get('pageName') == 'Menu') {
            $url = Tk_Type_Url::createUrl('/page/Home');
        }
        $this->setRedirect($url);
    }
}
/**
 * Dis-engagepage lock on form event
 *
 * @author godar
 */
class Wik_Form_Event_Lock extends Form_Event
{
    static function create()
    {
        $obj = new self();
        return $obj;
    }

    function init()
    {
        $this->setTrigerList(array('add', 'save', 'update'));
    }

    function execute()
    {
        $page = $this->getForm()->getObject();
        if ($page->getLock()) {
            $page->getLock()->unlock(Auth::getUser()->getId());
        }
    }
}
