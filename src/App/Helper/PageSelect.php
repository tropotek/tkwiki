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
     * @var string
     */
    protected $buttonSelector = '';

    /**
     * @var string
     */
    protected $inputSelector = '';

    
    /**
     * constructor.
     */
    public function __construct($buttonSelector, $inputSelector)
    {
        $this->buttonSelector = $buttonSelector;
        $this->inputSelector = $inputSelector;
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = $this->getTemplate();

        $template->appendJsUrl(\Tk\Uri::create(\Tk\Config::getInstance()->getTemplateUrl() . '/js/jquery-jtable.js'));
        
        $listUrl = \Tk\Uri::create('/ajax/getPageList');
        $js = <<<JS
jQuery(function($) {

  // required for the pageSelect renderer
  config.pageSelect = {
    button : $('{$this->buttonSelector}'),
    input : $('{$this->inputSelector}')
  };

  
  $('.jtable').jtable({
    properties : ['title', 'modified'],
    dataUrl : '$listUrl',
    onSelect : function (object) {
      config.pageSelect.input.val(object.url);
      $('#pageSelectModal').modal('hide');
    }
  });
  
  // show dialog trigger
  config.pageSelect.button.on('click', function(e) {
    $('#pageSelectModal').modal('show');
  });
  
});
JS;
        $template->appendJs($js);
        
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
        
        <div class="jtable"></div>
        
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