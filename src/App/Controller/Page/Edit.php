<?php
namespace App\Controller\Page;

use App\Db\Content;
use App\Db\ContentMap;
use App\Db\Lock;
use App\Db\Page;
use App\Db\PageMap;
use App\Helper\HtmlFormatter;
use App\Helper\PageSelect;
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

    /**
     * the session ID for the referring page
     */
    const SID_REFERRER = 'edit_ref';

    protected ?Page $wPage = null;

    protected ?Content $wContent = null;

    protected ?HtmlFormatter $formatter = null;

    protected Lock $lock;


    public function __construct()
    {
        parent::__construct($this->getFactory()->getPublicPage());
        $this->getPage()->setTitle('Edit Page');
        $this->lock = new Lock($this->getAuthUser());

        // TODO:
        //$this->setAccess(User::PERM_MANAGE_USER | User::PERM_MANAGE_STAFF);
    }

    public function doDefault(Request $request)
    {
        $referer = $this->getRequest()->server->get('HTTP_REFERER', '');
        if (!$this->getSession()->has(self::SID_REFERRER) && $referer) {
            $this->getSession()->set(self::SID_REFERRER, $referer);
        }

        // Find requested page
        $this->wPage = PageMap::create()->find($request->query->get('id') ?? 0);

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
        // Create a new Nav page
        if ($request->query->has('type') && Page::canCreate($this->getAuthUser())) {
            $this->wPage = new Page();
            $this->wPage->setType(Page::TYPE_NAV);
            $this->wPage->setUserId($this->getAuthUser()->getId());
            $this->wPage->setTitle('Menu Item');
            $this->wPage->setPermission(\App\Db\Page::PERM_PUBLIC);
            $this->wContent = new Content();
            $this->wContent->setUser($this->getAuthUser()->getId());
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
        // TODO: this lock should give up access if the user is the same as the user that is currently locked....
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

        // Acquire page lock.
        $this->lock->lock($this->wPage->getId());

        if (!$this->wContent) {
            $this->wContent = \App\Db\Content::cloneContent($this->wPage->getContent());
            // Execute the pre-formatter
            try {
                if ($this->wContent->html) {
                    $this->formatter = new HtmlFormatter($this->wContent->getHtml(), false);
                    //vd($this->wContent->html);
                    $this->wContent->html = $this->formatter->getHtml();
                    //vd($this->wContent->html);
                }
            } catch(\Exception $e) {
                Alert::addInfo($e->getMessage());
            }
        }

        if ($request->query->has('del')) {
            $this->doDelete($request);
        }


        // Get the form template
        $this->setForm(Form::create('page'));


        $group = 'Details';
        $this->getForm()->appendField(new Field\Hidden('pid'))
            ->setGroup($group);

        $this->getForm()->appendField(new Field\Input('title'))
            ->setRequired()
            ->setGroup($group);

        /** @var Field\Select $permission */
        $permission = $this->getForm()->appendField(new Field\Select('permission', Page::PERM_LIST))
            ->appendOption('-- Select --', '')
            ->setRequired()
            ->setGroup($group);
        if ($this->wPage && $this->wPage->getUrl() == Page::getHomeUrl()) {
            $permission->setDisabled();
        }

        $this->getForm()->appendField(new Field\Textarea('html'))
            ->addCss('mce')
            ->setGroup($group);

        if ($this->wPage->getType() == Page::TYPE_PAGE) {
            $group = 'Extra';
            $this->getForm()->appendField(new Field\Input('keywords'))
                ->setRequired()
                ->setGroup($group);

            $this->getForm()->appendField(new Field\Input('description'))
                ->setRequired()
                ->setGroup($group);
        }

        $this->getForm()->appendField(new Field\Textarea('css'))
            ->addCss('css-edit')
            ->setGroup($group);

        $this->getForm()->appendField(new Field\Textarea('js'))
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
        $homeUrl = $this->wPage->getHomeUrl();

        $this->lock->unlock($this->wPage->getId());
        $url = \Tk\Uri::create($homeUrl);
        if ($this->getFactory()->getCrumbs()->getBackUrl()) {
            $url = $this->getFactory()->getCrumbs()->getBackUrl();
        }
        if ($this->wPage && $this->wPage->type != \App\Db\Page::TYPE_NAV) {
            $url = $this->wPage->getPageUrl();
        }
        vd($url);
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
        if ($this->wContent->getHtml())
            $this->indexLinks($this->wPage, new HtmlFormatter($this->wContent->getHtml(), false));

        // Remove page lock
        $this->lock->unlock($this->wPage->getId());

        Alert::addSuccess('Page save successfully.');
        $url = $this->wPage->getPageUrl();
        if ($this->wPage->getType() == \App\Db\Page::TYPE_NAV) {
            $homeUrl = $this->wPage->getHomeUrl();
            $url = \Tk\Uri::create($homeUrl);
            if ($this->getSession()->has(self::SID_REFERRER)) {
                $url = $this->getSession()->get(self::SID_REFERRER);
                $this->getSession()->remove(self::SID_REFERRER);
            }
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

    protected function indexLinks(Page $page, HtmlFormatter $formatter): void
    {
        PageMap::create()->deleteLinkByPageId($page->getId());
        $nodeList = $formatter->getDocument()->getElementsByTagName('a');
        /** @var \DOMElement $node */
        foreach ($nodeList as $node) {
            $regs = array();
            if (preg_match('/^page:\/\/(.+)/i', $node->getAttribute('href'), $regs)) {
                if (isset ($regs[1])) {
                    PageMap::create()->insertLink($page->getId(), $regs[1]);
                }
            }
        }
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->appendText('title', $this->getPage()->getTitle());
        $template->setAttr('back', 'href', $this->wPage->getPageUrl());

        $template->setVisible($this->wPage->getType());

//        $header = new \App\Helper\PageHeader($this->wPage, $this->wPage->getContent(), $this->getAuthUser());
//        $template->insertTemplate('header', $header->show());

        //$template->appendTemplate('content', $this->form->getRenderer()->getTemplate());
        $template->appendTemplate('content', $this->getFormRenderer()->show());

        $dialog = new PageSelect();
        $template->appendBodyTemplate($dialog->show());

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