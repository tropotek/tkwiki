<?php
namespace App\Helper;


/**
 * Class PageSelect
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class PageSelect extends \Dom\Renderer\Renderer
{


    /**
     * constructor.
     */
    public function __construct()
    {
        
        
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = $this->getTemplate();

        $template->appendJsUrl(\Tk\Uri::create('/html/js/jquery-pageList.js'));
//        
//        $listUrl = \Tk\Uri::create('/ajax/getPageList');
//        
//        $js = <<<JS
//jQuery(function($) {
//  
//  $('.pageList').pageList({
//    ajaxUrl : '$listUrl',
//    onPageSelect : function (page) {
//      console.log(page);
//    }
//  })
//  
//});
//JS;
//        $template->appendJs($js);
        
        return $template;
    }
    


    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="modal fade" id="pageSelectModal" tabindex="-1" role="dialog" aria-labelledby="pageSelectModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="pageSelectModalLabel">Select A Page</h4>
      </div>
      <div class="modal-body">
        
        <div class="pageList"></div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
XHTML;
        return \Dom\Loader::load($xhtml);
    }
    
    
    
}