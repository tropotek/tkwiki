<?php
namespace App\Helper;

use Dom\Template;
use Tk\Traits\SystemTrait;

class PageSelect extends \Dom\Renderer\Renderer
{
    use SystemTrait;

    protected string $buttonSelector = '';

    protected string $inputSelector = '';


    public function __construct(string $buttonSelector, string $inputSelector)
    {
        $this->buttonSelector = $buttonSelector;
        $this->inputSelector = $inputSelector;
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();

        // TODO: Get the correct URL
        //$template->appendJsUrl(\Tk\Uri::create(\Tk\Config::getInstance()->getTemplateUrl() . '/app/js/jquery-jtable.js'));
        
        $listUrl = \Tk\Uri::create('/ajax/getPageList');        // TODO: Could this be handled in here???
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

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
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
HTML;
        return $this->loadTemplate($html);
    }

}