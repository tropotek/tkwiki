<?php
namespace App\Controller\Page;

use Tk\Request;
use App\Controller\Iface;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use App\Helper\HtmlFormatter;

/**
 * Class Index
 *
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
     * 
     */
    public function __construct()
    {
        parent::__construct('');
    }

    /**
     * @param Request $request
     * @return \App\Page\Iface
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        if (!\App\Factory::getSession()->has(self::SID_REFERRER) && $request->getReferer()) {
            \App\Factory::getSession()->set(self::SID_REFERRER, $request->getReferer());
        }
        // Find requested page

        $this->wPage = \App\Db\PageMap::create()->find($request->get('pageId'));

        // Create a new page
        if (!$this->wPage && $request->has('u') && $this->getUser()->getAcl()->canCreate()) {
            $this->wPage = new \App\Db\Page();
            $this->wPage->userId = $this->getUser()->id;
            $this->wPage->url = $request->get('u');
            $this->wPage->title = str_replace('_', ' ', $this->wPage->url);
            $this->wPage->permission = \App\Db\Page::PERMISSION_PUBLIC;
            $this->wContent = new \App\Db\Content();
            $this->wContent->userId = $this->getUser()->id;
        }
        // Create a new Nav page
        if ($request->has('type') && $this->getUser()->getAcl()->canCreate()) {
            $this->wPage = new \App\Db\Page();
            $this->wPage->type = \App\Db\Page::TYPE_NAV;
            $this->wPage->userId = $this->getUser()->id;
            $this->wPage->title = 'Menu Item';
            $this->wPage->permission = \App\Db\Page::PERMISSION_PUBLIC;
            $this->wContent = new \App\Db\Content();
            $this->wContent->userId = $this->getUser()->id;
        }
        if (!$this->wPage) {
            throw new \Tk\HttpException(404, 'Page not found');
        }
        
        // check if the user can edit the page
        $error = false;
        if (!$this->getUser()->getAcl()->canEdit($this->wPage)) {
            \Tk\Alert::addWarning('You do not have permission to edit this page.');
            $error = true;
        }
        if ($this->wPage->id && !\App\Factory::getLockMap()->canAccess($this->wPage->id)) {
            \Tk\Alert::addWarning('The page is currently being edited by another user. Try again later.');
            $error = true;
        }
        if ($error) {
            $url = $this->wPage->getUrl();
            if (!$this->wPage->id) {
                $url = \Tk\Uri::create('/');
            }
            $url->redirect();
        }

        // Acquire page lock.
        \App\Factory::getLockMap()->lock($this->wPage->id);

        
        if (!$this->wContent) {
            $this->wContent = \App\Db\Content::cloneContent($this->wPage->getContent());
            // Execute the pre-formatter (TODO: This could be an event)
            try {
                if ($this->wContent->html) {
                    $this->formatter = new HtmlFormatter($this->wContent->html, false);
                    $this->wContent->html = $this->formatter->getHtml();
                }
            } catch(\Exception $e) {
                \Tk\Alert::addInfo($e->getMessage());
            }
        }
        
        if ($request->has('del')) {
            $this->doDelete($request);
        }
        //vd($this->wContent->html);
        // Form
        $this->form = new Form('pageEdit', $request);
        $this->form->addField(new Field\Hidden('pid', $this->wPage->id));
        $this->form->addField(new Field\Input('title'))->setRequired(true);
        $this->form->addField(new Field\Textarea('html'));
        $this->form->addField(new Field\Select('permission'));
        
        if ($this->wPage->type == \App\Db\Page::TYPE_PAGE) {
            $this->form->addField(new Field\Input('keywords'));
            $this->form->addField(new Field\Input('description'));
        }
        $this->form->addField(new Field\Textarea('css'));
        $this->form->addField(new Field\Textarea('js'));

        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('cancel', array($this, 'doCancel')));

        $this->form->load(\App\Db\PageMap::create()->unmapForm($this->wPage));
        $this->form->load(\App\Db\ContentMap::create()->unmapForm($this->wContent));

        $this->form->execute();
        
        return $this->show($request);
    }

    /**
     * @param $form
     */
    public function doCancel($form)
    {
        $url = $this->wPage->getUrl();
        if ($this->wPage->type == \App\Db\Page::TYPE_NAV) {
            $url = \Tk\Uri::create('/');
        }
        \App\Factory::getLockMap()->unlock($this->wPage->id); 
        $url->redirect();
    }
    
    /**
     * @param Form $form
     */
    public function doSubmit($form)
    {
        \App\Db\PageMap::create()->mapForm($form->getValues(), $this->wPage);
        \App\Db\ContentMap::create()->mapForm($form->getValues(), $this->wContent);
        
        $form->addFieldErrors($this->wPage->validate());
        $form->addFieldErrors($this->wContent->validate());

        if ($this->wPage->url == \App\Db\Page::getHomeUrl()) {
            $this->wPage->url = 'Home';
            $this->wPage->permission = 0;
        }
        
        if ($form->hasErrors()) {
            return;
        }
        
        $this->wPage->save();
        $this->wContent->pageId = $this->wPage->id;
        $this->wContent->save();
        
        // Index page links
        if ($this->wContent->html)
            $this->indexLinks($this->wPage, new HtmlFormatter($this->wContent->html, false));
        
        // Remove page lock
        \App\Factory::getLockMap()->unlock($this->wPage->id);

        $url = $this->wPage->getUrl();
        if ($this->wPage->type == \App\Db\Page::TYPE_NAV) {
            \Tk\Uri::create('/');
            if (\App\Factory::getSession()->has(self::SID_REFERRER)) {
                $url = \App\Factory::getSession()->getOnce(self::SID_REFERRER);
            }

        }

        $url->redirect();
    }

    /**
     * @param Request $request
     */
    public function doDelete(Request $request)
    {
        $page = \App\Db\PageMap::create()->find($request->get('del'));
        if (!$page || !$this->getUser() || !$this->getUser()->getAcl()->canDelete($page)) {
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
     */
    protected function indexLinks($page, $formatter)
    {
        \App\Db\PageMap::create()->deleteLinkByPageId($page->id);
        $nodeList = $formatter->getDocument()->getElementsByTagName('a');
        foreach ($nodeList as $node) {
            $regs = array();
            if (preg_match('/^page:\/\/(.+)/i', $node->getAttribute('href'), $regs)) {
                if (isset ($regs[1])) {
                    \App\Db\PageMap::create()->insertLink($page->id, $regs[1]);
                }
            }
        }
    }

    /**
     * Note: no longer a dependency on show() allows for many show methods for many 
     * controller methods (EG: doAction/showAction, doSubmit/showSubmit) in one Controller object
     * 
     * @param Request $request
     * @return \App\Page\Iface
     */
    public function show(Request $request)
    {
        $template = $this->getTemplate();
        $domForm = $template->getForm('pageEdit');

        if ($this->wPage->url == \App\Db\Page::getHomeUrl()) {
            $field = $domForm->getFormElement('permission');
            $field->setAttribute('disabled', 'true')->setAttribute('title', 'Home page permissions must be public.');
        }
        $template->setChoice($this->wPage->type);

        $header = new \App\Helper\PageHeader($this->wPage, $this->wPage->getContent(), $this->getUser());
        $template->insertTemplate('header', $header->show());

        // Render the form
        $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
        $ren->show();
        
        return $this->getPage()->setPageContent($template);
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
              <button type="submit" name="save" value="save" class="btn btn-primary btn-sm"><i class="glyphicon glyphicon-save"></i> Save</button>
              <!-- button type="submit" name="delete" value="delete" class="btn btn-danger btn-sm wiki-delete-trigger"><i class="glyphicon glyphicon-remove"></i> Delete</button -->
              <button type="submit" name="cancel" value="cancel" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-ban-circle"></i> Cancel</button>
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