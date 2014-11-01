<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Renders a form and its fields.
 *
 * @package Com
 */
class Com_Form_Renderer extends Com_Web_Renderer
{

    /**
     * @var Com_Form_Object
     */
    private $form = null;

    /**
     * __construct
     *
     * @param Dom_Template $template
     * @param Com_Form_Object $form The form to renderer.
     */
    function __construct(Com_Form_Object $form)
    {
        $this->form = $form;
    }

    /**
     * Show
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $formName = $this->form->getId();
        $domForm = $template->getForm($formName);
        if ($domForm == null) {
            error_log('Cannot find form: ' . $formName);
            return;
        }
        $action = $this->form->getAction();
        if ($action != null) {
            $domForm->setAction($action);
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
        /* @var $field Com_Form_Field */
        foreach ($this->form->getFields() as $field) {
            $values = $field->getDomValues();
            foreach ($values as $name => $value) {
                if (is_Array($value)) {
                    $elList = $domForm->getFormElementList($name . '[]');
                } else {
                    $elList = $domForm->getFormElementList($name);
                }
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