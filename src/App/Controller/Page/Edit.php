<?php
namespace App\Controller\Page;

use App\Db\Page;
use App\Helper\Crumbs;
use Tk\Request;
use App\Controller\Iface;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use App\Helper\HtmlFormatter;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 *
 * @todo One issue here is that a page can be created from any random URL even if it does not exist on a page.
 * @todo We would have to change the page urls to include a flag to indicate it came from a page and we should create
 *       a new one, if none present then we can use the page not found error. Not urgent but a nice to have feature.
 */
class Edit extends Iface
{
    /**
     * the session ID for the referring page
     */
    const SID_REFERRER = 'edit_ref';

    /**
     * @var \App\Db\Page
     */
    protected $wPage = null;

    /**
     * @var \App\Db\Content
     */
    protected $wContent= null;

    /**
     * @var HtmlFormatter
     */
    protected $formatter = null;

    /**
     * @var \Tk\Form
     */
    protected $form = null;



    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Edit Page');

        if (!$this->getConfig()->getSession()->has(self::SID_REFERRER) && $request->getReferer()) {
            $this->getConfig()->getSession()->set(self::SID_REFERRER, $request->getReferer());
        }

        // Find requested page
        $this->wPage = \App\Db\PageMap::create()->find($request->get('pageId'));

        // Create a new page
        if (!$this->wPage && $request->has('u') && Page::canCreate($this->getAuthUser())) {
            $this->wPage = new \App\Db\Page();
            $this->wPage->userId = $this->getAuthUser()->getVolatileId();
            $this->wPage->url = $request->get('u');
            $this->wPage->title = str_replace('_', ' ', $this->wPage->getUrl());
            $this->wPage->permission = \App\Db\Page::PERMISSION_PRIVATE;
            $this->wContent = new \App\Db\Content();
            $this->wContent->userId = $this->getAuthUser()->getId();
        }
        // Create a new Nav page
        if ($request->has('type') && Page::canCreate($this->getAuthUser())) {
            $this->wPage = new \App\Db\Page();
            $this->wPage->type = \App\Db\Page::TYPE_NAV;
            $this->wPage->userId = $this->getAuthUser()->getId();
            $this->wPage->title = 'Menu Item';
            $this->wPage->permission = \App\Db\Page::PERMISSION_PUBLIC;
            $this->wContent = new \App\Db\Content();
            $this->wContent->userId = $this->getAuthUser()->getId();
        }
        if (!$this->wPage) {
            throw new \Tk\HttpException(404, 'Page not found');
        }


        // check if the user can edit the page
        $error = false;
        if (!$this->wPage->canEdit($this->getAuthUser())) {
            \Tk\Alert::addWarning('You do not have permission to edit this page.');
            $error = true;
        }
        if ($this->wPage->id && !$this->getConfig()->getLockMap()->canAccess($this->wPage->getId())) {
            \Tk\Alert::addWarning('The page is currently being edited by another user. Try again later.');
            $error = true;
        }
        if ($error) {
            $url = $this->wPage->getPageUrl();
            if (!$this->wPage->getId()) {
                $url = \Tk\Uri::create('/');
            }
            $url->redirect();
        }

        // Acquire page lock.
        $this->getConfig()->getLockMap()->lock($this->wPage->getId());

//vd($this->getBackUrl());
        if (!$this->wContent) {
            $this->wContent = \App\Db\Content::cloneContent($this->wPage->getContent());
            // Execute the pre-formatter (TODO: This could be an event)
            try {
                if ($this->wContent->html) {
                    $this->formatter = new HtmlFormatter($this->wContent->getHtml(), false);
                    //vd($this->wContent->html);
                    $this->wContent->html = $this->formatter->getHtml();
                    //vd($this->wContent->html);
                }
            } catch(\Exception $e) {
                \Tk\Alert::addInfo($e->getMessage());
            }
        }

        if ($request->has('del')) {
            $this->doDelete($request);
        }

        // Form
        $this->form = Form::create('pageEdit');
        $this->form->appendField(new Field\Hidden('pid', $this->wPage->getId()));
        $this->form->appendField(new Field\Input('title'))->setRequired(true);
        $this->form->appendField(new Field\Textarea('html'))->addCss('mce');
        $this->form->appendField(new Field\Select('permission'));

        if ($this->wPage->getType() == \App\Db\Page::TYPE_PAGE) {
            $this->form->appendField(new Field\Input('keywords'));
            $this->form->appendField(new Field\Input('description'));
        }
        $this->form->appendField(new Field\Textarea('css'));
        $this->form->appendField(new Field\Textarea('js'));

        $this->form->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->appendField(new Event\Submit('cancel', array($this, 'doCancel')));

        $this->form->load(\App\Db\PageMap::create()->unmapForm($this->wPage));
        $this->form->load(\App\Db\ContentMap::create()->unmapForm($this->wContent));

        $this->form->execute();

    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function doCancel($form)
    {
        $this->getConfig()->getLockMap()->unlock($this->wPage->getId());

        $url = $this->wPage->getPageUrl();

        if (!$this->wPage->getId()) {
            $url = Crumbs::getInstance()->getLast();
            if (!$url)
                $url = $this->getBackUrl();
        }
        if ($this->wPage->type == \App\Db\Page::TYPE_NAV || !$url) {
            $url = \Tk\Uri::create('/');
        }
        $url->redirect();
    }

    /**
     * @param Form $form
     * @param \Tk\Form\Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        \App\Db\PageMap::create()->mapForm($form->getValues(), $this->wPage);
        \App\Db\ContentMap::create()->mapForm($form->getValues(), $this->wContent);

        $form->addFieldErrors($this->wPage->validate());
        $form->addFieldErrors($this->wContent->validate());

        if ($this->wPage->getUrl() == \App\Db\Page::getHomeUrl()) {
            $this->wPage->setUrl('Home');
            $this->wPage->setPermission(0);
        }
        if (!$this->wContent->getHtml()) return;

        if ($form->hasErrors()) {
            return;
        }


        $this->wPage->save();
        $this->wContent->setPageId($this->wPage->getId());
        $this->wContent->save();

        // Index page links
        if ($this->wContent->getHtml())
            $this->indexLinks($this->wPage, new HtmlFormatter($this->wContent->getHtml(), false));

        // Remove page lock
        $this->getConfig()->getLockMap()->unlock($this->wPage->getId());

        $url = $this->wPage->getPageUrl();
        if ($this->wPage->getType() == \App\Db\Page::TYPE_NAV) {
            $url = \Tk\Uri::create('/');
            if ($this->getConfig()->getSession()->has(self::SID_REFERRER)) {
                $url = $this->getConfig()->getSession()->getOnce(self::SID_REFERRER);
            }

        }
        $event->setRedirect($url);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDelete(Request $request)
    {
        /** @var \App\Db\Page $page */
        $page = \App\Db\PageMap::create()->find($request->get('del'));
        //if (!$page || !$this->getAuthUser() || !$this->getConfig()->getAcl()->canDelete($page)) {
        if (!$page || !$this->getAuthUser() || !$page->canDelete($this->getAuthUser())) {
            \Tk\Alert::addWarning('You do not have the permissions to delete this page.');
            return;
        }
        $page->delete();
        // Redirect to homepage
        \Tk\Uri::create('/')->redirect();
    }

    /**
     *
     * @param \App\Db\Page $page
     * @param HtmlFormatter $formatter
     * @throws \Exception
     */
    protected function indexLinks($page, $formatter)
    {
        \App\Db\PageMap::create()->deleteLinkByPageId($page->getId());
        $nodeList = $formatter->getDocument()->getElementsByTagName('a');
        /** @var \DOMElement $node */
        foreach ($nodeList as $node) {
            $regs = array();
            if (preg_match('/^page:\/\/(.+)/i', $node->getAttribute('href'), $regs)) {
                if (isset ($regs[1])) {
                    \App\Db\PageMap::create()->insertLink($page->getId(), $regs[1]);
                }
            }
        }
    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();
        $domForm = $template->getForm('pageEdit');

        if ($this->wPage->getUrl() == \App\Db\Page::getHomeUrl()) {
            $field = $domForm->getFormElement('permission');
            $field->setAttribute('disabled', 'true')->setAttribute('title', 'Home page permissions must be public.');
        }
        $template->show($this->wPage->getType());

        $header = new \App\Helper\PageHeader($this->wPage, $this->wPage->getContent(), $this->getAuthUser());
        $template->insertTemplate('header', $header->show());

        // Render the form
        $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
        $ren->show();


        $saveEvent = $this->form->getField('save')->getEventName();
        $formId = $this->form->getId();

        $js = <<<JS
config.pageEdit = {
  formId : '$formId',
  saveEvent : '$saveEvent'
};
JS;
        $template->appendJs($js, array('data-jsl-priority' => -999));

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>
  <div var="header" class="wiki-header"></div>

    <div class="row wiki-edit" var="wiki-edit">
      <form class="form-horizontal" id="pageEdit" method="post">

        <div class="col-md-9">
          <div class="col-md-12">
            <div class="form-group">
              <label for="fid-title" class="control-label">Title:</label>
              <input type="text" id="fid-title" name="title" class="form-control"/>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <textarea name="html" id="fid-html" class="form-control tinymce" style="min-height: 500px"></textarea>
            </div>
          </div>
        </div>

        <div class="col-md-3 well">
          <div class="col-md-12">
            <div class="form-group">
              <label for="fid-permission" class="control-label">Permission:</label>
              <select class="form-control" id="fid-permission" name="permission">
                <option value="0">Public</option>
                <option value="1">Protected</option>
                <option value="2">Private</option>
              </select>
            </div>
          </div>
          <div class="col-md-12" choice="page">
            <div class="form-group">
              <label for="fid-keywords" class="control-label">Keywords:</label>
              <input type="text" class="form-control" id="fid-keywords" name="keywords"/>
            </div>
          </div>
          <div class="col-md-12" choice="page">
            <div class="form-group">
              <label for="fid-description" class="control-label">Description:</label>
              <input type="text" class="form-control" id="fid-description" name="description" />
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group">
              <label for="fid-css" class="control-label">CSS:</label>
              <textarea name="css" id="fid-css" class="form-control" style=""></textarea>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label for="fid-js" class="control-label">Javascript:</label>
              <textarea name="js" id="fid-js" class="form-control" style=""></textarea>
            </div>
          </div>

          <div class="form-group">
            <div class="col-sm-12">
              <button type="submit" name="save" value="save" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save</button>
              <!-- button type="submit" name="delete" value="delete" class="btn btn-danger btn-sm wiki-delete-trigger"><i class="fa fa-remove"></i> Delete</button -->
              <button type="submit" name="cancel" value="cancel" class="btn btn-default btn-sm"><i class="fa fa-ban"></i> Cancel</button>
            </div>
          </div>

        </div>

      </form>
    </div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}
