<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */


/**
 * A base component object.
 *
 *
 * @package Module
 */
class Com_Modules_Error extends Com_Web_Component
{


    /**
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $obj = Tk_Session::get('Tk_Error');
        if ($obj) {
            $template->insertText('title', $obj['statusCode'] . ' ' . $obj['statusCodeText']);
            $template->insertText('msg', $obj['msg']);
        }

    }


}