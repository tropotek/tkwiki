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
class Adm_Modules_StatsBox extends Com_Web_Component
{

    /**
     * Show
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        
        $ver = file_get_contents($this->getConfig()->getSitePath() . '/VERSION');
        $totalBytes = Tk_Type_Path::diskSpace($this->getConfig()->getSitePath());

        $template->insertText('hostname', $_SERVER['HTTP_HOST']);
        $template->insertText('version', $ver);
        $template->insertText('os', PHP_OS);
        $template->insertText('hdd', Tk_Type_Path::bytes2String($totalBytes));
    }

}