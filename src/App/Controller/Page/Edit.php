<?php
namespace App\Controller\Page;

use App\Db\Content;
use App\Db\Lock;
use App\Db\Page;
use App\Helper\PageSelect;
use App\Helper\SecretSelect;
use Bs\ControllerPublic;
use Bs\Form;
use Dom\Template;
use Tk\Alert;
use Tk\Form\Action\Submit;
use Tk\Form\Field\Checkbox;
use Tk\Form\Field\Hidden;
use Tk\Form\Field\Input;
use Tk\Form\Field\InputButton;
use Tk\Form\Field\Option;
use Tk\Form\Field\Select;
use Tk\Form\Field\Textarea;
use Tk\Uri;

class Edit extends ControllerPublic
{

    protected ?Form    $form     = null;
    protected ?Page    $wPage    = null;
    protected ?Content $wContent = null;
    protected ?Lock    $lock     = null;

    public function doDefault(): void
    {
        $referrer = trim($_SERVER['HTTP_REFERER'] ?? '');
        $pageId   = intval($_GET['pageId'] ?? 0);
        $pageUrl  = trim($_GET['u'] ?? '');
        $delete   = intval($_GET['del'] ?? 0);

        $this->getPage()->setTitle('Edit Page');
        if (!$this->getFactory()->getAuthUser()) {
            Alert::addWarning('You are not logged in.');
            Page::getHomePage()->getUrl()->redirect();
        }

        $ref = Uri::create($referrer)->getRelativePath();
        if ($ref != '/pageManager') {
            $this->getPage()->setCrumbsEnabled(false);
        }

        $this->lock = new Lock($this->getAuthUser());

        // Find requested page
        $this->wPage = Page::find($pageId);

        if ($this->wPage && !$this->wPage->canEdit($this->getAuthUser())) {
            Alert::addWarning('You do not have permissions to edit `' . $this->wPage->title . '`');
            if ($this->wPage->canView($this->getAuthUser())) {
                $this->wPage->getUrl()->redirect();
            }
            Page::getHomePage()->getUrl()->redirect();
        }

        // Create a new page
        if (!$this->wPage && $pageUrl && Page::canCreate($this->getAuthUser())) {
            $this->wPage = new Page();
            $this->wPage->userId = $this->getAuthUser()->userId;
            $this->wPage->url = $pageUrl;
            $this->wPage->title = str_replace('_', ' ', $this->wPage->url);
            $this->wPage->permission = \App\Db\Page::PERM_PRIVATE;
            $this->wContent = new Content();
            $this->wContent->userId = $this->getAuthUser()->userId;
        }

        if (!$this->wPage) {
            Alert::addWarning('The page you are attempting to edit cannot be found.');
            Page::getHomePage()->getUrl()->redirect();
        }

        // check if the user can edit the page
        $error = false;
        if (!$this->wPage->canEdit($this->getAuthUser())) {
            Alert::addWarning('You do not have permission to edit this page.');
            $error = true;
        }

        if ($this->wPage->pageId && !$this->lock->canAccess($this->wPage->pageId)) {
            Alert::addWarning('The page is currently being edited by another user. Try again later.');
            $error = true;
        }
        if ($error) {
            $url = $this->wPage->getUrl();
            if (!$this->wPage->pageId) {
                $url = Uri::create('/');
            }
            $url->redirect();
        }

        if ($delete) {
            $this->doDelete($delete);
        }

        // Acquire page lock.
        $this->lock->lock($this->wPage->pageId);

        // If not a new page with new content
        if (!$this->wContent) {
            $this->wContent = \App\Db\Content::cloneContent($this->wPage->getContent());
        }

        // Set the form
        $this->form = new Form();

        $group = 'Details';
        $this->form->appendField(new Hidden('pid'));

        $this->form->appendField(new Input('title'))
            ->setRequired()
            ->setGroup($group);

        $this->form->appendField(new InputButton('category'))
            ->setNotes('(Optional) Use page categories to group pages and allow them to show in the category listing widget')
            ->addBtnCss('fa fa-chevron-down')
            ->setGroup($group);

        /** @var Select $permission */
        $permission = $this->form->appendField(new Select('permission', array_flip(Page::PERM_LIST)))
            ->setRequired()
            ->setNotes('Select who can view/edit/delete this page. <a href="/Wiki_How_To#getting_started" target="_blank" title="Permission help">Permission help</a>')
            ->setGroup($group)
            ->prependOption('-- Select --', '');

        if ($this->wPage && $this->wPage->url == Page::getHomePage()->url) {
            $permission->setDisabled();
        }

        $this->form->appendField(new Checkbox('titleVisible'))
            ->setLabel('')
            ->addOnShowOption(function (Template $template, Option $option, $var) {
                $option->setName('Show Page Title');
            })
            ->setGroup($group);

        $this->form->appendField(new Checkbox('published'))
            ->setLabel('')
            ->setGroup($group);

        $this->form->appendField(new Textarea('html'))
            ->addCss('mce')
            ->removeCss('form-control')
            ->setGroup($group);

        $group = 'Extra';

        $list = $this->getConfig()->get('wiki.templates', []);
        $this->form->appendField(new Select('template', $list))
            ->setRequired()
            ->setGroup($group)
            ->prependOption('-- Site Default --', '');

        $this->form->appendField(new Input('keywords'))
            ->setRequired()
            ->setGroup($group);

        $this->form->appendField(new Input('description'))
            ->setRequired()
            ->setGroup($group);

        $this->form->appendField(new Textarea('js'))
            ->setLabel('Page JavaScript')
            ->addCss('js-edit')
            ->setGroup($group);

        $this->form->appendField(new Textarea('css'))
            ->setLabel('Page Stylesheet')
            ->addCss('css-edit')
            ->setGroup($group);

        $this->form->appendField(new Submit('save', [$this, 'onSubmit']));
        $this->form->appendField(new Submit('cancel', [$this, 'onCancel']))
            ->addCss('btn-outline-secondary');


        $load = array_merge(
            $this->form->unmapValues($this->wContent),
            $this->form->unmapValues($this->wPage)
        );
        $this->form->setFieldValues($load);

        $this->form->execute($_POST);

    }

    public function onCancel(Form $form, Submit $action): void
    {
        $this->lock->unlock($this->wPage->pageId);

        $url = $this->getFactory()->getBackUrl();
        if ($this->wPage->pageId && $_GET['e']) {
            $url = $this->wPage->getUrl();
        }
        $action->setRedirect($url);
    }

    public function onSubmit(Form $form, Submit $action): void
    {
        // TODO: check this works as expected
        $form->mapValues($this->wPage);
        $form->mapValues($this->wContent);

        $form->addFieldErrors($this->wPage->validate());
        $form->addFieldErrors($this->wContent->validate());

        if ($form->hasErrors()) {
            Alert::addError('Form contains errors.');
            return;
        }

        $this->wContent->html = mb_convert_encoding($this->wContent->html, 'UTF-8');
        $this->wPage->save();

        // only save content if it changes
        $currContent = $this->wPage->getContent();
        if (!$currContent || $this->wContent->diff($currContent)) {
            $this->wContent->pageId = $this->wPage->pageId;
            $this->wContent->save();
        }

        // Index page links
        if (trim($this->wContent->html)) {
            Page::indexPage($this->wPage);
        }

        // Remove page lock
        $this->lock->unlock($this->wPage->pageId);

        Alert::addSuccess('Page save successfully.');

        $url = $this->getFactory()->getBackUrl();
        if ($_GET['e']) {
            $url = $this->wPage->getUrl();
        }
        $action->setRedirect($url);
    }

    public function doDelete($pageId): void
    {
        $page = Page::find($pageId);
        if ($page && $page->canDelete($this->getAuthUser())) {
            $page->delete();
            Page::getHomePage()->getUrl()->redirect();
        }
        \Tk\Alert::addWarning('You do not have the permissions to delete this page.');
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());

        $url = $this->getFactory()->getBackUrl();
        if ($this->wPage->pageId && isset($_GET['e'])) {
            $url = $this->wPage->getUrl();
        }
        $template->setAttr('back', 'href', $url);

        $this->form->addCss('page-form');

        $this->form->getField('category')->addFieldCss('col-sm-6');
        $this->form->getField('permission')->addFieldCss('col-sm-6');
        $this->form->getField('titleVisible')->addFieldCss('col-sm-6');
        $this->form->getField('published')->addFieldCss('col-sm-6');
        $this->form->getField('keywords')->addFieldCss('col-sm-6');
        $this->form->getField('description')->addFieldCss('col-sm-6');
        $template->appendTemplate('content', $this->form->show());

        $dialog = new PageSelect();
        $template->appendBodyTemplate($dialog->show());

        if ($this->getRegistry()->get('wiki.enable.secret.mod', false)) {
            $dialog = new SecretSelect();
            $template->appendBodyTemplate($dialog->show());
        }

        // Autocomplete js
        $jsPageId = json_encode($this->wPage->pageId);
        $js = <<<JS
jQuery(function($) {
    let pageId = {$jsPageId}
    let cache = {};
    let input = $('[name=category]');

    input.autocomplete({
      source: function(request, response) {
        let term = request.term;
        if (term in cache) {
          response(cache[term]);
          return;
        }
        $.getJSON(tkConfig.baseUrl + '/api/page/category', request, function(data, status, xhr) {
          cache[term] = data;
          response(data);
        });
      },
      minLength: 0  // Must be 0 for dropdown btn to work
    });

    // Show the dropdown on click
    $('.fld-category button').on('click', function () {
        input.autocomplete('search', input.val());
    });

    // Start page lock trigger
    var lockTimeout = 1000*60;     // 1000 = 1 sec
    function saveLock() {
        $.getJSON(tkConfig.baseUrl + '/api/lock/refresh', {pid: pageId});
        setTimeout(saveLock, lockTimeout);
    }
    setTimeout(saveLock, lockTimeout);
});
JS;
        $template->appendJs($js);

        // Leave page confirm
        $js = <<<JS
jQuery(function($) {

    $(document).data('pageUpdated', false);

    $('input,select,textarea', '.page-form').on('change', function(e) {
        //console.log(e);
        $(document).data('pageUpdated', true);
    });

    $(window).on('beforeunload', function(e) {
        if ($(document).data('pageUpdated')) {
            return "Are you sure you want to exit?";
        }
    });

    $('button#page-cancel, button#page-save').on('click', function(){
        $(document).data('pageUpdated', false);
    });
});
JS;

        $template->appendJs($js);

        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<div>
  <div class="page-actions card mb-3">
    <div class="card-header"><i class="fa fa-cogs"></i> Actions</div>
    <div class="card-body" var="actions">
      <a href="/" title="Back" class="btn btn-outline-secondary" var="back"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-users"></i> </div>
    <div class="card-body wk-page-edit" var="content"></div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}