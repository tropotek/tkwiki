<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The dynamic form renderer
 *
 *
 * @package Form
 */
class Form_Renderer extends Dom_Renderer
{

    const MSG_CLASS_ERROR     = 'error';
    const MSG_CLASS_WARNING   = 'warning';
    const MSG_CLASS_NOTICE    = 'notice';


    /**
     * @var Form
     */
    protected $form = null;



    /**
     * Create the object instance
     *
     * @param Form $form
     */
    function __construct($form)
    {
        $this->form = $form;
    }

    /**
     * Get the form object
     *
     * @return Form
     */
    function getForm()
    {
    	return $this->form;
    }

    /**
     * Render
     *
     */
    function show()
    {
        $t = $this->getTemplate();

        $t->setAttr('form', 'id', $this->form->getFormId());
        $t->addClass('form', 'edit');
        if ($this->form->getMethod()) {
            $t->setAttr('form', 'method', $this->form->getMethod());
        }
        if ($this->form->getEnctype()) {
            $t->setAttr('form', 'enctype', $this->form->getEnctype());
        }
        if ($this->form->getEncoding()) {
            $t->setAttr('form', 'accept-charset', $this->form->getEncoding());
        }
        if ($this->form->getAction()) {
            $t->setAttr('form', 'action', $this->form->getAction());
        }
        if ($this->form->getTarget()) {
            $t->setAttr('form', 'target', $this->form->getTarget());
        }
        if ($this->form->getCssClass()) {
            $t->addClass('form', $this->form->getCssClass());
        }
        if ($this->form->getTitle()) {
            $t->insertText('title', $this->form->getTitle());
            $t->setChoice('title');
        }
        if (count($this->form->getAttrList())) {
            foreach ($this->form->getAttrList() as $name => $value) {
                $t->setAttr('form', $name, $value);
            }
        }

        // TODO: after showing messages use jquery to hide them after 30-60 sec
        $js = <<<JS
$(document).ready(function(){
//  setTimeout(function(){
//    $("div.formBox").fadeOut("slow", function () {
//      $("div.formBox").remove();
//    });
//  }, 10000);

  // Prevent Dbl click on submit
  $('form.edit').submit(function () {
    $(':submit', this).click(function() {
      return false;
    });
    $(':submit', this).attr('readonly', 'readonly');
    return true;
  });
});

JS;
        $t->appendJs($js);


        if ($this->form->hasMessages()) {
            $list = $this->form->getMessageList();
            $msgStr = '';
            foreach ($list as $name => $msg) {
                $msgStr .= '<em>Notice:</em> ' . $msg . "<br/>\n";
            }
            $t->insertHtml('notice', $msgStr);
            $t->setChoice('notice');
        }
        if ($this->form->hasErrors(false)) {
            $list = $this->form->getErrors();
            $msgStr = '';
            foreach ($list as $name => $msg) {
                $msgStr .= $msg . "<br/>\n";
            }
            $t->insertHtml('error', $msgStr);
            $t->setChoice('error');
        }

        if ($this->form->hasTabs()) {
            $t->addClass('form', 'tabs');
        }
        if (count($this->form->getHelpList())) {
            $t->setChoice('help');
            $i = 0;
            foreach ($this->form->getHelpList() as $title => $msg) {
                $r = $t->getRepeat('help');
                $r->insertText('title', $title);
                if ($i == (count($this->form->getHelpList())-1)) {
                    $r->addClass('msg', 'last');
                }
                $r->insertHtml('msg', $msg);
                $r->appendRepeat();
                $i++;
            }
        }

        $this->showEvents($t);
        $this->showFields($t);

    }

    /**
     * Render Buttons/Events
     *
     * @param Dom_Template $t
     */
    function showEvents($t)
    {
        $eventList = $this->form->getEventList();
        $eventList = array_reverse($eventList);
        /* @var $event Form_Event */
        foreach ($eventList as $event) {
            if ($event->getTemplate() && $event->getRender()) {
                $event->show($event->getTemplate());
                if ($event->getField()) {
                    $event->getField()->getTemplate()->appendTemplate('events', $event->getTemplate());
                } else {
                    $t->appendTemplate('events', $event->getTemplate());
                }
            }
        }
    }

    /**
     * Render Fields
     *
     * @param Dom_Template $t
     */
    function showFields($t)
    {
        $i = 0;
        $tabGroups = array();
        /* @var $field Form_Field */
        foreach ($this->form->getFieldList() as $field) {
            $field->show($field->getTemplate());
            if (!$field->getTabGroup()) {
                if ($field->getTemplate()->keyExists('var', 'block')) {
                    $class = ($i%2) ? 'odd' : 'even';
                    $field->getTemplate()->addClass('block', $class);
                }
                $t->appendTemplate('fields', $field->getTemplate());
                $i++;
            } else {
                if (!isset($tabGroups[$field->getTabGroup()])) {
                    $tabGroups[$field->getTabGroup()] = array();
                }
                $tabGroups[$field->getTabGroup()][] = $field;
            }
        }

        $i = (count($tabGroups)%2) ? 0 : 1;
        foreach ($tabGroups as $gname => $group) {

            $tab = $t->getRepeat('tab');
            $tab->setAttr('tabUrl', 'href', '#' . $this->form->getId().$this->cleanName($gname));
            $glabel = trim(ucfirst(preg_replace('/[A-Z][a-z]/', ' $0', $gname)));
            $tab->insertText('tabUrl', $glabel);
            if ($this->getForm()->tabGroupHasErrors($gname)) {
                $tab->addClass('tab', 'error');
            }

            $tab->appendRepeat();

            $tabBox = $t->getRepeat('tabBox');
            foreach ($group as $field) {
                if ($field->getTemplate()->keyExists('var', 'block')) {
                    $class = ($i%2) ? 'even' : 'odd';
                    $field->getTemplate()->addClass('block', $class);
                }
                $tabBox->setAttr('tabBox', 'id', $this->form->getId().$this->cleanName($gname));
                $tabBox->appendTemplate('tabBox', $field->getTemplate());
                $i++;
            }
            $tabBox->appendRepeat();
        }

        if (count($tabGroups)) {
            $t->setChoice('tabs');
            $tabPainName = $this->form->getId().'-tabPane';
            $t->setAttr('tabs', 'id', $tabPainName);
            $t->appendJs("$(document).ready(function() { $('#$tabPainName').tabs().find('.ui-tabs-nav'); }); ");
        }
    }

    private function cleanName($str)
    {
        return str_replace(' ', '_', $str);
    }


    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = sprintf('<?xml version="1.0"?>
<form var="form">
  <h3 var="title" choice="title"></h3>

  <div class="formBox noticeBox" var="notice" choice="notice"></div>
  <div class="formBox errorBox" var="error" choice="error"></div>

  <div class="eventTop submit left" var="events"></div>
  <div class="admFieldBox" var="fields">
    <div class="formNotes" choice="help">
      <div repeat="help">
        <h4 var="title"></h4>
        <p var="msg"></p>
      </div>
    </div>

    <div class="tabs" var="tabs" choice="tabs">
      <ul>
        <li repeat="tab" var="tab"><a href="#" var="tabUrl"></a></li>
      </ul>
      <div var="tabBox" repeat="tabBox"></div>
    </div>

  </div>
  <div class="eventBottom submit left" var="events"></div>

</form>
');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }



}