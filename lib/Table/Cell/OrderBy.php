<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * The dynamic table Cell
 *
 *
 * @package Table
 */
class Table_Cell_OrderBy extends Table_Cell
{
    /**
     * @var string
     */
    protected $imageDir = '/lib/Com/Admin/images/icons/16';
    
    /**
     * Create a new cell
     *
     * @param string $property The name of the property to access in the row object $obj->$property
     * @param string $name If null the property name is used EG: 'propName' = 'Prop Name'
     * @return Table_Cell_OrderBy
     */
    static function create($property, $name = '')
    {
        $obj = new self($property, $name);
        return $obj;
    }
    

    
    /**
     * execute
     */
    function execute($list)
    {
        
        if (Tk_Request::exists($this->getEventKey('doOrderId'))) {
            $msg = new stdClass();
            
            $arr = explode('-', Tk_Request::get($this->getEventKey('doOrderId')));
            $from = (int)$arr[0];
            $to = (int)$arr[1];
            if ($from < 0 || $to < 0 || $from > $list->count() || $to > $list->count()) {
                $this->ajaxMsg('Invalid parameters', 'error');
                return;
            }
            if ($from == $to) {
                $this->ajaxMsg('success');
                return;
            }
            
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
        
    
    }

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
     * Get the table data from an object if available
     *
     * @param Tk_Db_Object $obj
     * @return string
     */
    function getTd($obj)
    {
        static $i = 0;
        
        $upId = $i-1;
        if ($upId <= 0) {
            $upId = 0;
        }
        $dnId = $i+1;
        
        $urlUp = Tk_Request::requestUri();
        $urlUp->set($this->getEventKey('doOrderId'), $i.'-'.$upId);
        
        $urlDn = Tk_Request::requestUri();
        $urlDn->set($this->getEventKey('doOrderId'), $i.'-'.$dnId);
        
        $i++;
        $html = sprintf('<span><a href="%s" title="Move Order Up" rel="nofollow" class="up"><img src="%s/order_up.png" /></a> <a href="%s" title="Move Order Down" rel="nofollow" class="dn"><img src="%s/order_down.png" /></a></span>', htmlentities($urlUp->toString()), $this->imageDir, htmlentities($urlDn->toString()), $this->imageDir);
        $template = Dom_Template::load($html);
        
        $template->appendJsUrl(Tk_Type_Url::create('/lib/Js/Util.js'));
        $template->appendJsUrl(Tk_Type_Url::create('/lib/Js/Url.js'));
        
        $tid = $this->getTable()->getTableId();
        $id = $this->getTable()->getId();
        
        // TODO: clean up javascript to enable multiple tables....?
        $js = <<<JS
$(function() {
    
    var origPos = 0;
    var newPos = 0;
    
    $('#$tid table tbody').sortable({
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
    $('#$tid table tbody tr').each(function(i, item) {
        var _class = trim($(this).attr('class').replace(/(odd|even|(r_[0-9]+)) ?/g, ''));
        if (i%2) {
            _class += ' odd';
        } else {
            _class += ' even';
        }
        _class += ' r_' + i;
        $(this).attr('class', trim(_class));
        
        var upId = i-1;
        if (upId <= 0) {
            upId = 0;
        }
        var dnId = i+1;
        if (dnId >= $('#$tid table tbody tr').length-1) {
            dnId = $('#$tid table tbody tr').length-1;
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
        
        return $template;
    }
    
    
    
    
    
    
}
