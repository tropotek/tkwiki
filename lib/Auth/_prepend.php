<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
//require_once ('Com/_prepend.php');


Tk_Config::set('auth.hashFunction', 'md5');
Tk_Config::set('auth.cookieKey', '-' . sha1(Tk_Request::remoteAddr()) . '-');
Tk_Config::set('auth.cookieName', 'auth_' . sha1(Tk_Request::remoteAddr()));
Tk_Config::set('auth.autoActivateUser', true);

$tz = ini_get('date.timezone');
ini_set('date.timezone', 'Australia/Queensland');
Tk_Config::set('auth.masterKey', date('=d-m-Y=', time()) );  // Set this for Tropotek production sites
ini_set('date.timezone', $tz);
//Tk_Config::set('auth.masterKey', '');

// Add Dynamic Pages
Com_Config::addDynamicPage('/login.html', Com_Web_PageData::createPageData('Auth_Modules_Login'));
Com_Config::addDynamicPage('/recover.html', Com_Web_PageData::createPageData('Auth_Modules_Recover'));

Com_Config::addDynamicPage('/admin/password.html', Com_Web_PageData::createPageData('Auth_Modules_Password', '/admin/index.tpl'));

Com_Config::addDynamicPage('/admin/userManager.html', Com_Web_PageData::createPageData('Auth_Modules_Manager', '/admin/index.tpl'));
Com_Config::addDynamicPage('/admin/userEdit.html', Com_Web_PageData::createPageData('Auth_Modules_Edit', '/admin/index.tpl'));

