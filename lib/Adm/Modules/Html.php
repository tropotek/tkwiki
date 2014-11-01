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
class Adm_Modules_Html extends Com_Web_Component
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
    registerSlideBox('cbox3');
    registerSlideBox('cbox4');

    $('#tabs').tabs().find('.ui-tabs-nav');

    $('#dialog').dialog({
        width: 500,
        height: 200,
        modal: true,
        autoOpen: false,
        buttons: {'OK': function () { $(this).dialog('close'); } }
    });

    $('#dialogTrigger').click(function () {
        $('#dialog').dialog('open');
    });
});

JS;
        $template->appendJs($js);

    }

}