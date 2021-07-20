<?php
namespace App\Form\Renderer;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * @deprecated Kept so we can easily use if needed. but try to refactor sites that use it to use new FieldGroup object
 */
class FieldGroup extends \Tk\Form\Renderer\FieldGroup
{

    /**
     * @return \Dom\Renderer\Renderer|\Dom\Template|null
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();
        return $template;
    }

    /**
     * @param \Dom\Template $template
     */
    protected function showField($template)
    {
        $html = $this->getField()->getTemplate();
        if ($html instanceof \Dom\Template) {
            $template->replaceTemplate('element', $html);
        } else {
            $template->replaceHtml('element', $html);
        }
    }

    /**
     * @param \Dom\Template $template
     */
    protected function showErrors($template)
    {
        if ($this->getField()->hasErrors()) {
            $template->addCss('form-group', 'has-error is-invalid has-feedback');
            $estr = '';
            foreach ($this->getField()->getErrors() as $error) {
                if ($error)
                    $estr .= $error . "<br/>\n";
            }
            if ($estr) {
                $estr = substr($estr, 0, -6);
                $template->appendHtml('errorText', $estr);
                $template->show('errorText');
            }
        }
    }

    /**
     * @param \Dom\Template $template
     */
    protected function showLabel($template)
    {
        if ($this->getField()->hasShowLabel() && $this->getField()->getLabel() !== null) {
            $label = $this->getField()->getLabel();
            if ($label) $label .= ':';
            if ($this->getField()->isRequired()) {
                $template->addCss('form-group', 'required');
                $template->setAttr('label', 'title', 'Required');
            }
            $template->appendHtml('label', $label);
            $template->setAttr('label', 'for', $this->getField()->getAttr('id'));
            $template->show('label');
        }
    }

    /**
     * @param \Dom\Template $template
     */
    protected function showNotes($template)
    {
        if ($this->getField()->getNotes() !== null) {
            $template->show('notes');
            $template->appendHtml('notes', $this->getField()->getNotes());
        }
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    protected function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="form-group form-group-sm" var="form-group">
  <label class="control-label" var="label" choice="label"></label>
  <span class="help-block error-block"><span class="" var="errorText" choice="errorText"></span></span>
  <div var="element" class="controls"></div>
  <span class="help-block help-text" var="notes" choice="notes">&nbsp;</span>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}
