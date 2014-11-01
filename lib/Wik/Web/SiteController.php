<?php

/*
 * This file is part of the DkLib.
 *   You can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   You should have received a copy of the GNU Lesser General Public License
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A config/registry object that configures the site functionality.
 *
 *
 * @package Wik
 */
class Wik_Web_SiteController implements Tk_Util_ControllerInterface
{
    
    /**
     * @var Com_Web_ComponentController
     */
    protected $comCon = null;
    
    /**
     * __construct
     *
     * @param Com_Web_ComponentController $comCon
     */
    function __construct(Com_Web_ComponentController $comCon)
    {
        $this->comCon = $comCon;
    }
    
    /**
     * Do all pre-initalisation operations
     * This method called before the execution method
     *
     */
    function init()
    {
        $this->checkInstall();
    }
    
    /**
     * Execute the controller
     *
     */
    function execute()
    {
        $template = $this->comCon->getPageComponent()->getTemplate();
        $config = Wik_Config::getInstance();
        $settings = Wik_Db_Settings::getInstance();
        
        // Check if the install directory exists
        if (!$config->isDebugMode() && is_dir($config->getSitePath() . '/install')) {
            $body = $template->getBodyElement();
            $div = $body->ownerDocument->createElement('div', "The '/install' directory exists and is a security risk. Please delete the directory.");
            $div->setAttribute('style', 'font-size: 10px;border: 1px outset #ccc; background-color: #F99;padding: 2px 4px;font-family: arial,sans-serif;');
            $body->insertBefore($div, $body->firstChild);
        }
        
        $ver = $this->getVersion();
        $template->insertText('version', 'DkWiki Ver: ' . $ver);
        $template->appendMetaTag('Author', 'http://www.domtemplate.com/');
        $template->appendMetaTag('Project', 'PHP5 - DkWiki v' . $ver);
        
        Js_Ui_Jquery::create($template)->show();
        $template->appendJsUrl(Tk_Type_Url::createUrl('/lib/Js/Util.js'));
        $template->appendJsUrl(Tk_Type_Url::createUrl('/lib/Wik/js/jquery.passroids.js'));
        $template->appendCssUrl(Tk_Type_Url::createUrl('/lib/Adm/css/icons.css'));
        
        if ($config->getUserRegistrationEnabled()) {
            $template->setChoice('register');
        }
        
        $template->insertText('header', Wik_Db_Settings::getInstance()->getTitle());
        $title = $template->getTitleText();
        if (!$title) {
            $template->setTitleText(Wik_Db_Settings::getInstance()->getTitle());
        }
        
        if (Auth::getUser()) {
            $template->insertText('username', Auth::getUser()->getUsername());
            $template->setChoice('_logout');
        } else {
            $template->setChoice('_login');
        }
        
        if ($settings->getMetaDescription()) {
            $template->appendMetaTag('description', wordcat(strip_tags($settings->getMetaDescription()), 300));
        }
        if ($settings->getMetaKeywords()) {
            $template->appendMetaTag('keywords', $settings->getMetaKeywords());
        }
        if ($settings->getContact()) {
            $template->insertHtml('contact', nl2br($settings->getContact()));
        }
        
        
        // Setup fancybox lightbox for images.
        $template->appendCssUrl(Tk_Type_Url::create('/lib/Js/jquery/plugins/fancybox/jquery.fancybox-1.3.4.css'));
        $template->appendJsUrl( Tk_Type_Url::create('/lib/Js/jquery/plugins/fancybox/jquery.fancybox-1.3.4.pack.js'));
        $js = <<<JS
$(document).ready(function() {
    $("a.jdkImageUrl, a.lightbox, , a[href$='jpg'], a[href$='gif'], a[href$='jpeg'], a[href$='png'] ").fancybox({
        'transitionIn'  :   'elastic',
        'transitionOut' :   'elastic',
        'speedIn'       :   600,
        'speedOut'      :   200,
        'overlayShow'   :   true,
        'hideOnOverlayClick' : true,
        'hideOnContentClick' : true
    });
});
JS;
        $template->appendJs($js);
        
        if ($settings->getFooterScript()) {
            if ($settings->getFooterScript()) {
                $fjs = $settings->getFooterScript();
                $js = <<<JS
$(document).ready(function() {
  $fjs
});
JS;
                $template->appendJs($js);
            }
        }
    }
    
    /**
     * Do all post initalisation operations here
     * This method called after the execute method
     *
     */
    function postInit()
    {
        $template = $this->comCon->getPageComponent()->getTemplate();
        
        
        
    }
    
    /**
     * Check the install status of the site
     *
     */
    function checkInstall()
    {
        if (!Com_Config::getInstance()->isDebugMode() && is_dir(Com_Config::getInstance()->getSitePath() . '/install')) {
            try {
                $fileVer = trim(file_get_contents(Com_Config::getInstance()->getSitePath() . '/VERSION'));
                if ($this->getVersion() < $fileVer) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                $url = new Tk_Type_Url('/install/index.php');
                $url->redirect();
            }
        }
    }
    
    /**
     * Get the version from the database
     *
     * @return string
     */
    function getVersion()
    {
        $sql = "SELECT * FROM `version` ORDER BY `id` DESC LIMIT 0, 1";
        $db = Tk_Db_Factory::getDb();
        $result = $db->query($sql);
        $row = $result->current();
        if (!$row || count($row) == 0) {
            throw new Tk_ExceptionRuntime('Cannot get version info.');
        }
        return $row['version'];
    }

}
