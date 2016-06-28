<?php
namespace App\Controller\Page;

use Tk\Request;
use App\Controller\Iface;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form\Field\Option\ArrayObjectIterator;

/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends Iface
{

    /**
     * @var \App\Db\Page
     */
    protected $wPage = null;
    
    /**
     * @var \App\Db\Content
     */
    protected $wContent= null;

    /**
     * @var \Tk\Form
     */    
    protected $form = null;
    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('', array('edit', 'moderator', 'admin'));
    }

    /**
     * @param Request $request
     * @return \App\Page\Iface
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        
        $this->wPage = \App\Db\Page::getMapper()->find($request->get('pageId'));
        if (!$this->wPage) {
            /**
             * Check the org wiki 
             * I think the page is created after a page is saved and the 
             * link is found in the page content.
             * 
             * So in reality we should not be creating new page objects in this page.
             * 
             */
            throw new \Tk\Exception('NEW PAGES NOT IMPLEMENTED YET!!!!!!!!');
        }
        $this->wContent = \App\Db\Content::cloneContent($this->wPage->getContent());
//        if (!$this->wContent->pageId)
//            $this->wContent->pageId = $this->wPage->id;
//        $this->wContent = $this->wPage->getContent();
//        if (!$this->wContent) {
//            $this->wContent = new \App\Db\Content();
//            $this->wContent->userId = $this->getUser()->getId();
//            $this->wContent->pageId = $this->wPage->getId();
//        }
        
        // Form

        $this->form = new Form('pageEdit');

        $this->form->addField(new Field\Input('title'))->setRequired(true);
        $this->form->addField(new Field\Textarea('html'));
        $this->form->addField(new Field\Input('url'))->setRequired(true);
        
        $this->form->addField(new Field\Select('permission'));
        $this->form->addField(new Field\Input('keywords'));
        $this->form->addField(new Field\Input('description'));
        $this->form->addField(new Field\Textarea('css'));
        $this->form->addField(new Field\Textarea('js'));

        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('delete', array($this, 'doDelete')));
        $this->form->addField(new Event\Link('cancel', \Tk\Uri::create($this->wPage->url)));
        
        $this->form->load(\App\Db\PageMap::unmapForm($this->wPage));
        $this->form->load(\App\Db\ContentMap::unmapForm($this->wContent));
        
        $this->form->execute();
        
        if ($request->has('del') && $this->wPage) {
            $this->doDelete($this->form);
        }
        
        return $this->showDefault($request);
    }

    /**
     * @param Form $form
     */
    public function doSubmit($form)
    {

        \App\Db\PageMap::mapForm($form->getValues(), $this->wPage);
        \App\Db\ContentMap::mapForm($form->getValues(), $this->wContent);
        
        $form->addFieldErrors(\App\Db\PageValidator::create($this->wPage)->getErrors());
        $form->addFieldErrors(\App\Db\ContentValidator::create($this->wContent)->getErrors());
        
        if ($this->wPage->id == 1) {
            $this->wPage->url = 'Home';
            $this->wPage->permission = 0;
        }
        
        
        if ($form->hasErrors()) {
            return;
        }
        
        $this->wPage->save();
        $this->wContent->pageId = $this->wPage->id;
        $this->wContent->save();
        
        // Search the content for new pages and create them here....
        
        vd('--- Submitted ---');
        
    }

    /**
     * @param $form
     */
    public function doDelete($form)
    {

        vd('--- DO DELETE IF YOU DARE ---');
        
        // Redirect to homepage
        \Tk\Uri::create('/')->redirect();
    }
    
    
    
    
/*
  WYSIWYG Editors:
   - Code Mirror: https://codemirror.net/   (IDE)
   - TinyMCE: https://www.tinymce.com/ (We know this works with the plugins for page creation...)
   - Markitup: http://markitup.jaysalvat.com/ ()
   - bootstrap-wysihtml5: http://bootstrap-wysiwyg.github.io/bootstrap3-wysiwyg/
   
  File Managers:
   - elFinder: http://studio-42.github.io/elFinder/#elf_l1_SW1hZ2Vz (Used it and it works well.)
*/

    /**
     * Note: no longer a dependency on show() allows for many show methods for many 
     * controller methods (EG: doAction/showAction, doSubmit/showSubmit) in one Controller object
     * 
     * @param Request $request
     * @return \App\Page\PublicPage
     */
    public function showDefault(Request $request)
    {
        $template = $this->getTemplate();
        $domForm = $template->getForm('pageEdit');

        if ($this->wPage->id == 1) {
            $field = $domForm->getFormElement('url');
            $field->setAttribute('disabled', 'true')->setAttribute('title', 'Home page URL must be static.');
            $field = $domForm->getFormElement('permission');
            $field->setAttribute('disabled', 'true')->setAttribute('title', 'Home page permissions must be public.');
        }
        
        
        $header = new \App\Helper\PageHeader($this->wPage, $this->getUser());
        $template->insertTemplate('header', $header->show());


        // Render the form
        $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
        $ren->show();
        
        
        
        // Fix disabled buttons
        $js = <<<JS
jQuery(function($) {


});
JS;
        //$template->appendJs($js);
        
        
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
              <label for="fid-url" class="control-label">Url:</label>
              <div class="input-group">
                <input type="text" id="fid-url" name="url" class="form-control"/>
                <div class="input-group-btn">
                  <a href="#" class="btn btn-default wiki-create-url-trigger" title="Auto Generate page URL from Title"><i class="glyphicon glyphicon-link"></i></a>
                </div>
              </div>
            </div>
          </div>
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
          <div class="col-md-12">
            <div class="form-group">
              <label for="fid-keywords" class="control-label">Keywords:</label>
              <input type="text" class="form-control" id="fid-keywords" name="keywords"/>
            </div>
          </div>
          <div class="col-md-12">
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