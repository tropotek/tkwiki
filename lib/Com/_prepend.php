<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

require_once ("Tk/Autoloader.php");
if (!Tk_Config::$instance) {
    Com_Config::getInstance();
}
require_once ('Tk/_prepend.php');

Com_Config::setTemplatePath(Tk_Config::getSitePath() . '/html');
Com_Config::setSslEnabled(true);
Com_Config::setLanguage('en_GB');

$tz = ini_get('date.timezone');
ini_set('date.timezone', 'Australia/Queensland');
Com_Config::setMasterKey(md5(date('=d-m-Y=', time())));
ini_set('date.timezone', $tz);

/*
 * Default Dynamic Template Setup
 * @TODO: still need to update the node modifier to update all admin links.
 */
Com_Config::setAdminPath('/admin');

// Add CMS Admin Pages
Com_Config::addDynamicPage('/index.html', Com_Web_PageData::createPageData());
Com_Config::addDynamicPage('/html.html', Com_Web_PageData::createPageData('Com_Modules_Html'));
//Com_Config::addDynamicPage('/error.html', Com_Web_PageData::createPageData('Com_Modules_Error'));
//Com_Config::addDynamicPage('/login.html', Com_Web_PageData::createPageData('Com_Auth_Login'));

Com_Config::addDynamicPage('/admin/dbStrReplace.html', Com_Web_PageData::createPageData('Adm_Modules_DbStrReplace', '/admin/index.tpl'));
//Com_Config::addDynamicPage('/admin/dbQuery.html', Com_Web_PageData::createPageData('Adm_Modules_DbQuery', '/admin/index.tpl'));

