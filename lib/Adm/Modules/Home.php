<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An admin content box. Put text and stats within these box's on the admin home page
 *
 * @package Com
 */
class Adm_Modules_Home extends Com_Web_Component
{

    /**
     * init
     *
     */
    function init()
    {


    }

    /**
     * Show
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $template->appendCss(".eventTop { display: none; }");

        $js = <<<JS


$(document).ready(function() {

    registerSlideBox('cbox1');
    registerSlideBox('cbox2');
});

JS;
        $template->appendJs($js);

    }

}