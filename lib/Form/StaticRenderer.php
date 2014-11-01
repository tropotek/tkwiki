<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The static form renderer.
 * It requires on the Dom_Form class
 *
 * This renderer requires that the form markup is already in place.
 *
 *
 * @package Form
 */
class Form_StaticRenderer extends Dom_Renderer
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
     * @param Dom_Template $template
     */
    function __construct($form, $template)
    {
        $this->form = $form;
        $this->setTemplate($template);
    }

    /**
     * Render
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();

        $domForm = $template->getForm($this->form->getId());
        if ($domForm == null) {
            Tk::log('Cannot find form: ' . $this->form->getId());
            return;
        }
        if ($this->form->getAction()) {
        	$domForm->setAction($this->form->getAction());
        }
        if ($this->form->getMethod()) {
        	$domForm->setMethod($this->form->getMethod());
        }
        if ($this->form->getEnctype()) {
        	$domForm->getNode()->setAttribute('enctype', $this->form->getEnctype());
        }
        if ($this->form->getEncoding()) {
        	$domForm->getNode()->setAttribute('accept-charset', $this->form->getEncoding());
        }

        $this->showFields($template, $domForm);

        $msg = '';
        if ($this->form->hasErrors()) {
            $msg = 'The form contains errors, please correct and try again. <br />';
        }
        foreach ($this->form->getErrors() as $m) {
            $msg .= $m . ' <br />';
        }
        if ($msg) {
            $msg = substr($msg, 0, -6);
        }

        if ($msg || count($this->form->getErrors()) > 0) {
            $var = $choice = 'form-error';
            if ($template->keyExists('var', $var)) {
                $template->insertHtml($var, $msg);
                $template->setChoice($choice);
            } else {
                $errNode = $domForm->getNode()->ownerDocument->createElement('p');
                $errNode->setAttribute('class', 'errorBox');
                if ($domForm->getNode()) {
                    $child = $this->getFirstChildElement($domForm->getNode());
                    $domForm->getNode()->insertBefore($errNode, $child);
                    Dom_Template::insertHtmlDom($errNode, $msg);
                }
            }
        }
    }


    /**
     * Render the form fields
     *
     * @param Dom_Template $template
     * @param Dom_Form $domForm
     * @return boolean
     */
    private function showFields($template, $domForm)
    {
        $hasErrors = false;
        /* @var $field Form_Field */
        foreach ($this->form->getFieldList() as $field) {
            if ($field instanceof Form_Field_Hidden) {
                $domEl = $domForm->getFormElement($field->getName());
                if (!$domEl) {
                    $domForm->appendHiddenElement($field->getName(), $field->getValue());
                }
            }


            $values = $field->getSubFieldValueList();
            foreach ($values as $name => $value) {
                if (is_Array($value)) {
                    $elList = $domForm->getFormElementList($name . '[]');
                } else {
                    $elList = $domForm->getFormElementList($name);
                }
                /* @var $el Dom_FormElement */
                foreach ($elList as $el) {
                    if ($el != null) {
                        $type = $el->getType();
                        switch (get_class($el)) {
                            case 'Dom_FormInput' :
                                if ($type == 'file') {
                                    break;
                                }
                                if ($type == 'checkbox' || $type == 'radio') {
                                    if (is_array($value) && $type == 'checkbox') {
                                        foreach ($value as $v) {
                                            $domForm->setCheckedByValue($name . '[]', $v);
                                        }
                                    } else {
                                        $domForm->setCheckedByValue($name, $value);
                                    }
                                } else {
                                    $el->setValue($value);
                                }
                                break;
                            case 'Dom_FormTextarea' :
                                $el->setValue($value);
                                break;
                            case 'Dom_FormSelect' :
                                $el->setValue($value);
                                break;
                        }
                    }
                }
            }

            // Render Errors
            if ($field->hasErrors()) {
                $msg = '';
                foreach ($field->getErrors() as $i => $m) {
                    $msg .= $m;
                    if ($i < count($field->getErrors()) - 1) {
                        $msg .= '<br/>';
                    }
                }

                if ($msg != null) {
                    $el = $domForm->getFormElement($name);
                    if ($el == null) {
                        throw new Tk_ExceptionNullPointer('No form element: `' . $name . '` found. Check your validation field name parameters.');
                    }
                    $node = $el->getNode();
                    if ($node->parentNode && (strstr($node->parentNode->getAttribute('class'), 'required') ||
                        $node->parentNode->getAttribute('class') == 'optional'))
                    {
                        $node->parentNode->setAttribute('class', $node->parentNode->getAttribute('class') . ' error');
                    }
                    $var = $field->getName() . '-error';
                    if ($template->keyExists('var', $var)) {
                        $template->setChoice($var);
                        if ($template->keyExists('var', $var)) {
                            if ($template->getText($var) == null) {
                                $template->replaceHTML($var, $msg);
                            }
                        }
                    } else {
                        $errNode = $node->ownerDocument->createElement('p');
                        $errNode->setAttribute('class', 'error');
                        if ($node->parentNode) {
                            $child = $this->getFirstChildElement($node->parentNode);
                            $errNode = $node->parentNode->insertBefore($errNode, $child);
                            Dom_Template::insertHtmlDom($errNode, $msg);
                        }
                    }
                    $hasErrors = true;
                }
            }
        }
        return $hasErrors;
    }

    /**
     * getFirstChildElement
     *
     * @param DOMElement $parent
     * @return DOMNode
     */
    function getFirstChildElement($parent)
    {
        foreach ($parent->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                return $node;
            }
        }
    }

}