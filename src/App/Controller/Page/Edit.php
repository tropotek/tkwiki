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
    protected ?Page    $page     = null;
    protected ?Content $content  = null;
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
        $this->page = Page::find($pageId);

        if ($this->page && !$this->page->canEdit($this->getAuthUser())) {
            Alert::addWarning('You do not have permissions to edit `' . $this->page->title . '`');
            if ($this->page->canView($this->getAuthUser())) {
                $this->page->getUrl()->redirect();
            }
            Page::getHomePage()->getUrl()->redirect();
        }

        // Create a new page
        if (!$this->page && $pageUrl && Page::canCreate($this->getAuthUser())) {
            $this->page = new Page();
            $this->page->userId = $this->getAuthUser()->userId;
            $this->page->url = $pageUrl;
            $this->page->title = str_replace('_', ' ', $this->page->url);
            $this->page->permission = \App\Db\Page::PERM_PRIVATE;
            $this->content = new Content();
            $this->content->userId = $this->getAuthUser()->userId;
        }

        if (!$this->page) {
            Alert::addWarning('The page you are attempting to edit cannot be found.');
            Page::getHomePage()->getUrl()->redirect();
        }

        // check if the user can edit the page
        $error = false;
        if (!$this->page->canEdit($this->getAuthUser())) {
            Alert::addWarning('You do not have permission to edit this page.');
            $error = true;
        }

        if ($this->page->pageId && !$this->lock->canAccess($this->page->pageId)) {
            Alert::addWarning('The page is currently being edited by another user. Try again later.');
            $error = true;
        }
        if ($error) {
            $url = $this->page->getUrl();
            if (!$this->page->pageId) {
                $url = Uri::create('/');
            }
            $url->redirect();
        }

        if ($delete) {
            $this->doDelete($delete);
        }

        // Acquire page lock.
        $this->lock->lock($this->page->pageId);

        // If not a new page with new content
        if (!$this->content) {
            $this->content = \App\Db\Content::cloneContent($this->page->getContent());
        }

        // Set the form
        $this->form = new Form();

        $group = 'Details';
        $this->form->appendField(new Hidden('pid'))->setReadonly();

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

        if ($this->page && $this->page->url == Page::getHomePage()->url) {
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

        // todo: Disabled to prevent cross site scripting attacks
        if ($this->getAuthUser()->isAdmin()) {
            $this->form->appendField(new Textarea('js'))
                ->setLabel('Page JavaScript')
                ->setNotes('Only admin users can add javascript')
                ->addCss('js-edit')
                ->setGroup($group);
        }

        $this->form->appendField(new Textarea('css'))
            ->setLabel('Page Stylesheet')
            ->addCss('css-edit')
            ->setGroup($group);

        $this->form->appendField(new Submit('save', [$this, 'onSubmit']));
        $this->form->appendField(new Submit('cancel', [$this, 'onCancel']))
            ->addCss('btn-outline-secondary');


        $load = array_merge(
            $this->form->unmapModel($this->content),
            $this->form->unmapModel($this->page)
        );
        $this->form->setFieldValues($load);

        $this->form->execute($_POST);

    }

    public function onCancel(Form $form, Submit $action): void
    {
        $this->lock->unlock($this->page->pageId);

        $url = $this->getFactory()->getBackUrl();
        if ($this->page->pageId && isset($_GET['e'])) {
            $url = $this->page->getUrl();
        }
        $action->setRedirect($url);
    }

    public function onSubmit(Form $form, Submit $action): void
    {
        $form->mapModel($this->page);
        $form->mapModel($this->content);

        $form->addFieldErrors($this->page->validate());
        $form->addFieldErrors($this->content->validate());

        if ($form->hasErrors()) {
            Alert::addError('Form contains errors.');
            return;
        }

        $this->content->html = mb_convert_encoding($this->content->html, 'UTF-8');
        $this->page->save();

        // only save content if it changes
        $currContent = $this->page->getContent();
        if (!$currContent || $this->content->diff($currContent)) {
            $this->content->pageId = $this->page->pageId;
            $this->content->save();
        }

        // Index page links
        if (trim($this->content->html)) {
            Page::indexPage($this->page);
        }

        // Remove page lock
        $this->lock->unlock($this->page->pageId);

        Alert::addSuccess('Page save successfully.');

        $url = $this->getFactory()->getBackUrl();
        if (isset($_GET['e'])) {
            $url = $this->page->getUrl();
        }
        $action->setRedirect($url);
    }

    public function doDelete($pageId): void
    {
        $page = Page::find($pageId);
        if ($page && $page->canEdit($this->getAuthUser())) {
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
        if ($this->page->pageId && isset($_GET['e'])) {
            $url = $this->page->getUrl();
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

        $pageSelect = new PageSelect();
        $template->appendBodyTemplate($pageSelect->show());

        if ($this->getRegistry()->get('wiki.enable.secret.mod', false)) {
            $secretSelect = new SecretSelect();
            $template->appendBodyTemplate($secretSelect->show());
        }

        // Autocomplete js
        $jsPageId = json_encode($this->page->pageId);
        $js = <<<JS
jQuery(function($) {
    let pageId = $jsPageId;
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

    $(document).on('save.mce', '.mce, .mce-min', function() {
        $(document).data('pageUpdated', false);
        $('#form_save').trigger('click');
    });

    // page select event
    $(document).on('selected.ps.modal', '#page-select-dialog', function(e, title, url, pageId) {
        const editor = tinymce.activeEditor;
        let attrs = {
          href: 'page://' + url,
          title: title
        };
        if (editor.selection.getContent()) {
          editor.execCommand('CreateLink', false, attrs);
        } else {
          editor.insertContent(editor.dom.createHTML('a', attrs, editor.dom.encode(title)));
        }
    });

    // category select event
    $(document).on('catSelect.ps.modal', '#page-select-dialog', function(e, category, attrs) {
        const editor = tinymce.activeEditor;
        editor.insertContent(editor.dom.createHTML('div', attrs,
            editor.dom.encode('{Category List: ' + category + '}'))
        );
    });

    // secret select event
    $(document).on('selected.ss.modal', '#secret-select-dialog', function(e, secretId, name) {
        const editor = tinymce.activeEditor;
        let linkAttrs = {
          class: 'wk-secret',
          'wk-secret': secretId,
          'title': name,
          src: tkConfig.baseUrl + '/html/assets/img/secretbg.png'
        };
        editor.insertContent(editor.dom.createHTML('img', linkAttrs));
    })

    // on editor save event


    // on window unload event
    $(document).data('pageUpdated', false);
    $('input,select,textarea', '.page-form').on('change', function(e) {
        $(document).data('pageUpdated', true);
    });
    $(window).on('beforeunload', function(e) {
        if ($(document).data('pageUpdated')) {
            return "Are you sure you want to exit?";
        }
    });
    $('button#form_cancel, button#form_save').on('click', function() {
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