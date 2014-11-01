<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
require_once ("Tk/Functions/autoLoad.php");
require_once ('Wik/_prepend.php');
require_once ('Auth/_prepend.php');

Tk_Config::set('auth.autoActivateUser', false);

Com_Config::addDynamicPage('/contactUs.html', Com_Web_PageData::createPageData('Ext_Modules_Form_Contact'));



