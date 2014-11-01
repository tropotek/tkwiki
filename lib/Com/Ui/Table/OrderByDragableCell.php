<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * Render an array of Dk objects to a table
 * This cell uses the event keys:
 *  o doOrder   - This is the order direction 'up' or 'down'
 *  o doOrderId - This is the object id to move
 *
 * @package Com
 */
class Com_Ui_Table_OrderByDragableCell extends Com_Ui_Table_Cell
{
    
    protected $imageDir = '/lib/Com/Admin/images/icons/16';
    
    /**
     * ajaxMsg
     *
     * @param $msg
     * @param $status
     */
    function ajaxMsg($msg, $status = 'ok')
    {
        if (Tk_Request::exists('_ajx')) {
            $obj = new stdClass();
            $obj->msg = $msg;
            echo json_encode($obj);
            exit;
        } else {
            $url = Tk_Request::requestUri();
            $url->delete($this->getEventKey('doOrderId'));
            $url->redirect();
        }
    }
    
    /**
     * doProcess
     */
    function doProcess()
    {
        
        if (Tk_Request::exists($this->getEventKey('doOrderId'))) {
            $msg = new stdClass();
            
            $arr = explode('-', Tk_Request::get($this->getEventKey('doOrderId')));
            $from = (int)$arr[0];
            $to = (int)$arr[1];
            if ($from == $to) {
                $this->ajaxMsg('success');
                return;
            }
            $list = $this->getTable()->getList();
            
            $fromObj = $list->get($from);
            $toObj = $list->get($to);
            
            if (!$fromObj || !$toObj) {
                $this->ajaxMsg('Null object found', 'error');
                return;
            }
            
            $listOrder = array();
            /* @var $obj Tk_Db_Object */
            foreach ($list as $obj) {
                $listOrder[] = $obj->getOrderBy();
            }
            
            $fromObj->setOrderBy($toObj->getOrderBy());
            $fromObj->save();
            if ($from > $to) {  // Order Dn
                /* @var $obj Tk_Db_Object */
                foreach ($list as $idx => $obj) {
                    if ($idx < $from && $idx >= $to) {
                        $obj->setOrderBy($listOrder[$idx+1]);
                        $obj->save();
                    }
                }
            } else if ($from < $to) {   // Order Up
                /* @var $obj Tk_Db_Object */
                foreach ($list as $idx => $obj) {
                    if ($idx > $from && $idx <= $to) {
                        $obj->setOrderBy($listOrder[$idx-1]);
                        $obj->save();
                    }
                }
            }
            $this->ajaxMsg('success');
        }
        
        $template = $this->getTable()->getTemplate();
        $template->appendJsUrl(Tk_Type_Url::create('/lib/Js/Util.js'));
        $template->appendJsUrl(Tk_Type_Url::create('/lib/Js/Url.js'));
        
        $tid = '_table' . $this->getTable()->getId();
        $id = $this->getTable()->getId();
        
        $js = <<<JS
$(function() {
    
    var origPos = 0;
    var newPos = 0;
    
    $('table#$tid tbody').sortable({
        helper: function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        },
        stop: function (e, ui) {
          newPos = ui.item.prevAll().length;
          reorder();
          var url = new Url(ui.item.find('.up').attr('href'));
          url.addField('doOrderId_' + $id, origPos + '-' + newPos);
          $.get(url.toString(), {_ajx: true}, function (data) {
              
          });
          
          
        },
        start: function (e, ui) {
          origPos = ui.item.prevAll().length;
        }
    }).disableSelection();
    
});

function reorder()
{
    $('table#$tid tbody tr').each(function(i, item) {
        var class = trim($(this).attr('class').replace(/(odd|even|(r_[0-9]+)) ?/g, ''));
        if (i%2) {
            class += 'odd';
        } else {
            class += 'even';
        }
        class += ' r_' + i;
        $(this).attr('class', trim(class));
        
        var upId = i-1;
        if (upId <= 0) {
            upId = 0;
        }
        var dnId = i+1;
        if (dnId >= $('table#$tid tbody tr').length-1) {
            dnId = $('table#$tid tbody tr').length-1;
        }
        
        var upUrl = new Url($(this).find('.up').attr('href'));
        upUrl.addField('doOrderId_' + $id, i+'-'+upId);
        $(this).find('.up').attr('href', upUrl.toString())
        
        var dnUrl = new Url($(this).find('.dn').attr('href'));
        dnUrl.addField('doOrderId_' + $id, i+'-'+dnId);
        $(this).find('.dn').attr('href', dnUrl.toString());
    });
    
}
JS;
        $template->appendJs($js);
    
    }
    
    /**
     * Get the table data from an object if available
     *
     * @param Dk_Db_Object $obj
     * @return string
     */
    function getPropertyData($obj)
    {
        static $i = 0;
        
    
        $upId = $i-1;
        if ($upId <= 0) {
            $upId = 0;
        }
        $dnId = $i+1;
        if ($dnId >= $this->getTable()->getList()->count()-1) {
            $dnId = $this->getTable()->getList()->count()-1;
        }
        
        $urlUp = Tk_Request::getInstance()->getRequestUri();
        $urlUp->set($this->getEventKey('doOrderId'), $i.'-'.$upId);
        
        $urlDn = Tk_Request::getInstance()->getRequestUri();
        $urlDn->set($this->getEventKey('doOrderId'), $i.'-'.$dnId);
        
        $i++;
        return sprintf('<a href="%s" title="Move Order Up" rel="nofollow" class="up"><img src="%s/order_up.png" /></a> <a href="%s" title="Move Order Down" rel="nofollow" class="dn"><img src="%s/order_down.png" /></a>', htmlentities($urlUp->toString()), $this->imageDir, htmlentities($urlDn->toString()), $this->imageDir);
    }
    
}

/*
// Return a helper with preserved width of cells
// This is an alternate if required....
helper: function(e, tr)
  {
    var originals = tr.children();
    var helper = tr.clone();
    helper.children().each(function(index)
    {
      // Set helper cell sizes to match the original sizes
      $(this).width(originals.eq(index).width())
    });
    return helper;
  }
*/