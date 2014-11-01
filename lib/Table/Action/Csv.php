<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * For this action to work the object must contain a delete() method
 *
 *
 * @package Table
 * @todo: Work out a way to run the sql query again without any limits so we get all records in the CSV
 * @todo: we need the contents of the table not the contents of the object, exacly what is rendered to the table cells
 */
class Table_Action_Csv extends Table_Action
{
    
    protected $confirmMsg = 'Are you sure you want to export the table records.';
    
    /**
     * Create a delete action
     *
     * @return Table_Action_Delete
     */
    static function create()
    {
        $obj = new self('csv', Tk_Request::requestUri());
        $obj->addClass('i16-csv');
        $obj->setLabel('CSV Export');
        return $obj;
    }
    
    /**
     * setConfirm
     *
     * @param string $str
     * @return Table_Action_Delete
     */
    function setConfirm($str)
    {
        $this->confirmMsg = $str;
        return $this;
    }
    
    /**
     * (non-PHPdoc)
     * @param Tk_Loader_Collection $list
     * @see Table_Action::execute()
     */
    function execute($list)
    {
        // Headers for an download:
        $file = 'table.csv';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"'); 
        header('Content-Transfer-Encoding: binary');

        $out = fopen('php://output', 'w');
        $arr =  $list->getRawArray();
        foreach ($arr as $obj) {
            fputcsv($out, $obj);
        }
        fclose($out);
        exit;
    }

    /**
     * Get the action HTML to insert into the Table.
     * If you require to use form data be sure to submit the form using javascript not just a url anchor.
     * Use submitForm() found in Js/Util.js to submit a form with an event
     *
     * @param array $list
     * @return Dom_template You can also return HTML string
     */
    function getHtml($list)
    {
        $js = sprintf("$(this).unbind('click'); return confirm('%s');", $this->confirmMsg);
        $url = Tk_Request::requestUri()->set($this->getEventKey('csv'));
        return sprintf('<a class="%s" href="%s" onclick="%s" title="%s">%s</a>', $this->getClassString(), $url->toString(), $js, $this->notes, $this->label);
    }
    
    
}
