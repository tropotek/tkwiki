<?php
namespace App\Form\Field;

use Tk\Ui\Dialog\AjaxSelect;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PageSelect extends \Tk\Form\Field\InputGroup
{

    /**
     * @var null|AjaxSelect
     */
    protected $dialog = null;

    /**
     * @var null|\Tk\Ui\Button
     */
    protected $button = null;


    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->button = \Tk\Ui\Button::createButton($this->getLabel(), 'fa fa-folder-open');
        $this->button->setAttr('type', 'button');
        $this->button->setText('');
        $this->button->setAttr('title', 'Select Home Page');

        $this->dialog = new AjaxSelect('Select ' . $this->button->getAttr('title'));
        $this->dialog->addCss('wk-page-select-dialog');
        $this->dialog->addOnAjax(function (AjaxSelect $dialog) {
            $tool = \Tk\Db\Tool::create('title', 50);
            $keywords = trim(strip_tags($dialog->getRequest()->get('keywords')));
            // only public pages can be used as a homepage
            $filter = array('keywords' => $keywords, 'type' => \App\Db\Page::TYPE_PAGE, 'permission' => '0');
            $l = \App\Db\PageMap::create()->findFiltered($filter, $tool);
            $list = array();
            foreach ($l as $page) {
                $list[] = array('id' => $page->getUrl(), 'name' => '[/' . $page->getUrl(). '] ' . $page->getTitle());
            }
            return $list;
        });
        $this->dialog->execute();

        $this->button->setAttr('data-toggle', 'modal');
        $this->button->setAttr('data-target', '#'.$this->dialog->getId());

    }

    /**
     * @return AjaxSelect|null
     */
    public function getDialog(): ?AjaxSelect
    {
        return $this->dialog;
    }

    /**
     * @return \Tk\Ui\Button|null
     */
    public function getButton(): ?\Tk\Ui\Button
    {
        return $this->button;
    }

    /**
     * @return string|\Dom\Template
     */
    public function show()
    {
        $this->dialog->setAttr('data-field-id', $this->getId());
        $this->append($this->getButton()->show());

        $template = parent::show();
        $js = <<<JS
jQuery(function ($) {
  function escapeStr(str) {
    if (str)
        return str.replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,'\\\\$1');
    return str;
  }

  $('div.wk-page-select-dialog').each(function () {
    var dialog = $(this);
    dialog.data('itemOnClick', function () {
      var searchParams = new URLSearchParams($(this).attr('href'));
      if (searchParams.has('selectedId')) {
        var field = $('#' + escapeStr(dialog.data('fieldId')));
        if (field) {
          field.val(searchParams.get('selectedId'));
        }
        dialog.modal('hide');
      }
      return false;
    });
  });

});
JS;

        $template->appendJs($js);
        $template->appendBodyTemplate($this->getDialog()->show());

        return $template;
    }

    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="input-group">
  <div class="input-group-prepend input-group-btn" var="prepend" choice="prepend"></div>
  <input type="text" var="element" class="form-control" />
  <div class="input-group-append input-group-btn" var="append" choice="append"></div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}
