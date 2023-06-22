<?php
namespace App\Controller\Page;

use App\Db\Content;
use App\Db\ContentMap;
use App\Db\Lock;
use App\Db\Page;
use App\Db\PageMap;
use App\Helper\HtmlFormatter;
use App\Helper\PageSelect;
use App\Helper\SecretSelect;
use Bs\PageController;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Alert;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Action;
use Tk\Form\FormTrait;
use Tk\FormRenderer;
use Tk\Uri;

class Edit extends PageController
{
    use FormTrait;

    protected ?Page $wPage = null;

    protected ?Content $wContent = null;

    protected ?HtmlFormatter $formatter = null;

    protected Lock $lock;


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Edit Page');
        if (!$this->getFactory()->getAuthUser()) {
            Alert::addWarning('You are not logged in.');
            Uri::create(Page::getHomeUrl())->redirect();
        }
    }

    public function doDefault(Request $request)
    {
        $this->lock = new Lock($this->getAuthUser());

        // Find requested page
        $this->wPage = PageMap::create()->find($request->query->get('id') ?? 0);

        if ($this->wPage && !$this->wPage->canEdit($this->getAuthUser())) {
            Alert::addWarning('You do not have permissions to edit `' . $this->wPage->getTitle() . '`');
            if ($this->wPage->canView($this->getAuthUser())) {
                $this->wPage->getPageUrl()->redirect();
            }
            Uri::create(Page::getHomeUrl())->redirect();
        }

        // Create a new page
        if (!$this->wPage && $request->query->has('u') && Page::canCreate($this->getAuthUser())) {
            $this->wPage = new Page();
            $this->wPage->setUserId($this->getAuthUser()->getVolatileId());
            $this->wPage->setUrl($request->get('u'));
            $this->wPage->setTitle(str_replace('_', ' ', $this->wPage->getUrl()));
            $this->wPage->setPermission(\App\Db\Page::PERM_PRIVATE);
            $this->wContent = new Content();
            $this->wContent->setUserId($this->getAuthUser()->getId());
        }

        if (!$this->wPage) {
            Alert::addWarning('The page you are attempting to edit cannot be found.');
            Uri::create(Page::getHomeUrl())->redirect();
        }

        // check if the user can edit the page
        $error = false;
        if (!$this->wPage->canEdit($this->getAuthUser())) {
            Alert::addWarning('You do not have permission to edit this page.');
            $error = true;
        }

        if ($this->wPage->id && !$this->lock->canAccess($this->wPage->getId())) {
            Alert::addWarning('The page is currently being edited by another user. Try again later.');
            $error = true;
        }
        if ($error) {
            $url = $this->wPage->getPageUrl();
            if (!$this->wPage->getId()) {
                $url = Uri::create('/');
            }
            $url->redirect();
        }

        if ($request->query->has('del')) {
            $this->doDelete($request);
        }

        // Acquire page lock.
        $this->lock->lock($this->wPage->getId());

        // If not a new page with new content
        if (!$this->wContent) {
            $this->wContent = \App\Db\Content::cloneContent($this->wPage->getContent());
        }

        // Set the form
        $this->setForm(Form::create('page'));

        $group = 'Details';
        $this->getForm()->appendField(new Field\Hidden('pid'));

        $this->getForm()->appendField(new Field\Input('title'))
            ->setRequired()
            ->setGroup($group);

        $list = $this->getConfig()->get('wiki.templates', []);
        $this->getForm()->appendField(new Field\Select('template', $list))
            ->setRequired()
            ->setGroup($group)
            ->prependOption('-- Default --', '');

        $this->getForm()->appendField(new Field\InputButton('category'))
            ->setNotes('(Optional) Use page categories to group pages and allow them to show in the category listing widget')
            ->addBtnCss('fa fa-chevron-down')
            ->setGroup($group);

        /** @var Field\Select $permission */
        $permission = $this->getForm()->appendField(new Field\Select('permission', array_flip(Page::PERM_LIST)))
            ->setRequired()
            ->setNotes('Select who can view/edit/delete this page. <a href="/Wiki_How_To#getting_started" target="_blank" title="Permission help">Permission help</a>')
            ->setGroup($group)
            ->setStrict(true)
            ->prependOption('-- Select --', '');
        if ($this->wPage && $this->wPage->getUrl() == Page::getHomeUrl()) {
            $permission->setDisabled();
        }

        $this->getForm()->appendField(new Field\Checkbox('titleVisible'))
            ->setLabel('')
            ->addOnShowOption(function (\Dom\Template $template, \Tk\Form\Field\Option $option, $var) {
                $option->setName('Show Page Title');
            })
            ->setGroup($group);

        $this->getForm()->appendField(new Field\Checkbox('published'))
            ->setLabel('')
            ->setGroup($group);

        $this->getForm()->appendField(new Field\Textarea('html'))
            ->addCss('mce')
            ->removeCss('form-control')
            ->setGroup($group);

        $group = 'Extra';
        $this->getForm()->appendField(new Field\Input('keywords'))
            ->setRequired()
            ->setGroup($group);

        $this->getForm()->appendField(new Field\Input('description'))
            ->setRequired()
            ->setGroup($group);

        $this->getForm()->appendField(new Field\Textarea('css'))
            ->setLabel('Global Stylesheet')
            ->addCss('css-edit')
            ->setGroup($group);

        $this->getForm()->appendField(new Field\Textarea('js'))
            ->setLabel('Global JavaScript')
            ->addCss('js-edit')
            ->setGroup($group);

        $this->getForm()->appendField(new Action\Submit('save', [$this, 'onSubmit']));
        $this->getForm()->appendField(new Action\Submit('cancel', [$this, 'onCancel']))
            ->addCss('btn-outline-secondary');

        $load = PageMap::create()->getFormMap()->getArray($this->wPage);
        $this->getForm()->setFieldValues($load); // Use form data mapper if loading objects
        $load = ContentMap::create()->getFormMap()->getArray($this->wContent);
        $this->getForm()->setFieldValues($load); // Use form data mapper if loading objects

        $this->getForm()->execute($request->request->all());

        $this->setFormRenderer(new FormRenderer($this->getForm()));

        return $this->getPage();
    }

    public function onCancel(Form $form, Action\ActionInterface $action): void
    {
        $this->lock->unlock($this->wPage->getId());

        $url = \Tk\Uri::create($this->wPage->getHomeUrl());
        if ($this->getFactory()->getBackUrl()->getRelativePath() == '/pageManager') {
            $url = $this->getFactory()->getBackUrl();
        } else if ($this->wPage->getId()) {
            $url = $this->wPage->getPageUrl();
        }
        $action->setRedirect($url);
    }

    public function onSubmit(Form $form, Action\ActionInterface $action): void
    {
        PageMap::create()->getFormMap()->loadObject($this->wPage, $form->getFieldValues());
        ContentMap::create()->getFormMap()->loadObject($this->wContent, $form->getFieldValues());

        $form->addFieldErrors($this->wPage->validate());
        $form->addFieldErrors($this->wContent->validate());

        if ($form->hasErrors()) {
            Alert::addError('Form contains errors.');
            return;
        }

        $this->wContent->setHtml(mb_convert_encoding($this->wContent->getHtml(), 'UTF-8'));
        $this->wPage->save();

        // only save content if it changes
        $currContent = $this->wPage->getContent();
        if (!$currContent || $this->wContent->diff($currContent)) {
            $this->wContent->setPageId($this->wPage->getId());
            $this->wContent->save();
        }

        // Index page links
        if ($this->wContent->getHtml()) {
            $this->indexLinks($this->wPage, $this->wContent->getHtml());
        }

        // Remove page lock
        $this->lock->unlock($this->wPage->getId());

        Alert::addSuccess('Page save successfully.');

        $url = \Tk\Uri::create($this->wPage->getHomeUrl());
        if ($this->getFactory()->getBackUrl()->getRelativePath() == '/pageManager') {
            $url = $this->getFactory()->getBackUrl();
        } else if ($this->wPage->getId()) {
            $url = $this->wPage->getPageUrl();
        }
        $action->setRedirect($url);
    }

    public function doDelete(Request $request): void
    {
        /** @var Page $page */
        $page = PageMap::create()->find($request->get('del'));
        if ($page && $page->canDelete($this->getAuthUser())) {
            $page->delete();
            // Redirect to homepage
            $homeUrl = $this->wPage->getHomeUrl();
            \Tk\Uri::create($homeUrl)->redirect();
        }
        \Tk\Alert::addWarning('You do not have the permissions to delete this page.');
    }

    protected function indexLinks(Page $page, string $html): void
    {
        try {
            $doc = HtmlFormatter::parseDomDocument($html);
            PageMap::create()->deleteLinkByPageId($page->getId());
            $nodeList = $doc->getElementsByTagName('a');
            /** @var \DOMElement $node */
            foreach ($nodeList as $node) {
                $regs = [];
                if (preg_match('/^page:\/\/(.+)/i', $node->getAttribute('href'), $regs)) {
                    if (isset ($regs[1])) {
                        PageMap::create()->insertLink($page->getId(), $regs[1]);
                    }
                }
            }
        } catch (\Exception $e) { }
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());

        $url = $this->getFactory()->getBackUrl();
        if ($this->wPage->getId()) {
            $url = $this->wPage->getPageUrl();
        }
        $template->setAttr('back', 'href', $url);

        $this->getForm()->getField('title')->addFieldCss('col-sm-6');
        $this->getForm()->getField('template')->addFieldCss('col-sm-6');
        $this->getForm()->getField('category')->addFieldCss('col-sm-6');
        $this->getForm()->getField('permission')->addFieldCss('col-sm-6');
        $this->getForm()->getField('titleVisible')->addFieldCss('col-sm-6');
        $this->getForm()->getField('published')->addFieldCss('col-sm-6');
        $this->getForm()->getField('keywords')->addFieldCss('col-sm-6');
        $this->getForm()->getField('description')->addFieldCss('col-sm-6');
        $template->appendTemplate('content', $this->getFormRenderer()->show());

        $dialog = new PageSelect();
        $template->appendBodyTemplate($dialog->show());

        if ($this->getRegistry()->get('wiki.enable.secret.mod', false)) {
            $dialog = new SecretSelect();
            $template->appendBodyTemplate($dialog->show());
        }

        // Autocomplete js
        $jsPageId = json_encode($this->wPage->getId());
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
        $.getJSON(config.baseUrl + '/api/page/category', request, function(data, status, xhr) {
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
        $.getJSON(config.baseUrl + '/api/lock/refresh', {pid: pageId}, function(data) {});
        setTimeout(saveLock, lockTimeout);
    }
    setTimeout(saveLock, lockTimeout);
});
JS;
        $template->appendJs($js);


        // Leave page confirm
        $js = <<<JS
jQuery(function($) {
    setTimeout(function () {
        $('form#page').data('serialize', $('form#page').serialize());
        $(window).on('beforeunload', function(e) {
            if($('form#page').serialize() != $('form#page').data('serialize')) return true;
            else e=null;
        });
    }, 1000);
    $('button#page-cancel, button#page-save').on('click', function(){
        $(window).off('beforeunload');
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
  <div class="card mb-3">
    <div class="card-header"><i class="fa fa-cogs"></i> Actions</div>
    <div class="card-body" var="actions">
      <a href="/" title="Back" class="btn btn-outline-secondary" var="back"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header" var="title"><i class="fa fa-users"></i> </div>
    <div class="card-body" var="content"></div>
  </div>
</div>
HTML;
        return $this->loadTemplate($html);
    }

}