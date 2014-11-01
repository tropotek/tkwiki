<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Display a processing Icon while dissabling the page so no input can take place
 *
 * @package Com
 */
class Com_Ui_LightBox_Processing extends Com_Ui_LightBox_Component
{
    
    /**
     * __construct
     *
     * @param Tk_Type_Url $backgroundImg
     * @param Tk_Type_Url $loadingImg
     */
    function __construct($backgroundImg = null, $loadingImg = null)
    {
        
        if (!$backgroundImg) {
            $backgroundImg = new Tk_Type_Url('/lib/Com/Ui/LightBox/images/background.gif');
        }
        if (!$loadingImg) {
            $loadingImg = new Tk_Type_Url('/lib/Com/Ui/LightBox/images/icon1.gif');
        }
        
        $html = sprintf('<div class="processing"><img src="%s" alt="" /> Processing... </div>', $loadingImg->toString());
        parent::__construct($html, 250, 100, $backgroundImg);
        $this->hideHead(true);
    }

}