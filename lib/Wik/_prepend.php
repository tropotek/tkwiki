<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

require_once ("Tk/Functions/autoLoad.php");

if (!isset($config)) {
    $config = Wik_Config::getInstance();
}

// Include libs
require_once ('Com/_prepend.php');

$config->setSiteTitle('DkWiki');

Tk_Config::set( 'com.auth.masterKey', md5(date('=d-m-Y=', time())) );
Tk_Config::set('wik.emailCommentsToAdmin', true);



// Config Defaults
$config->setUserRegistrationEnabled(true);

$config->addDynamicPage('/index.html', Com_Web_PageData::createPageData('Wik_Modules_Page_View'));
$config->addDynamicPage('/login.html', Com_Web_PageData::createPageData('Wik_Auth_Login'));
$config->addDynamicPage('/changelog.html', Com_Web_PageData::createPageData('Wik_Modules_Changelog_View'));
$config->addDynamicPage('/edit.html', Com_Web_PageData::createPageData('Wik_Modules_Page_Edit'));
$config->addDynamicPage('/editDetails.html', Com_Web_PageData::createPageData('Wik_Modules_User_EditDetails'));
$config->addDynamicPage('/history.html', Com_Web_PageData::createPageData('Wik_Modules_Page_History'));
$config->addDynamicPage('/myPages.html', Com_Web_PageData::createPageData('Wik_Modules_Settings_Orphaned'));
$config->addDynamicPage('/orphaned.html', Com_Web_PageData::createPageData('Wik_Modules_Settings_Orphaned'));
$config->addDynamicPage('/search.html', Com_Web_PageData::createPageData('Wik_Modules_Search_Results'));
$config->addDynamicPage('/settings.html', Com_Web_PageData::createPageData('Wik_Modules_Settings_Manager'));


//$config->addDynamicPage('/changePassword.html', Com_Web_PageData::createPageData('Wik_Modules_User_ChangePassword'));

$config->addDynamicPage('/register.html', Com_Web_PageData::createPageData('Wik_Auth_Register'));
$config->addDynamicPage('/password.html', Com_Web_PageData::createPageData('Auth_Modules_Password'));

//$config->addDynamicPage('/signUp.html', Com_Web_PageData::createPageData('Wik_Modules_User_Signup'));
//$config->addDynamicPage('/signUpThanks.html', Com_Web_PageData::createPageData('Wik_Modules_User_Signup'));

$config->addDynamicPage('/userEdit.html', Com_Web_PageData::createPageData('Wik_Modules_User_Edit', '/index.tpl'));
$config->addDynamicPage('/userManager.html', Com_Web_PageData::createPageData('Wik_Modules_User_Manager', '/index.tpl'));
$config->addDynamicPage('/settingsEdit.html', Com_Web_PageData::createPageData('Wik_Modules_Settings_Edit', '/index.tpl'));


