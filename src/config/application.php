<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */

$config = \App\Config::getInstance();


/*
 * Template folders for pages
 */
$config['system.template.path'] = '/html';
$config['template.admin'] = $config['template.public'] = $config['system.template.path'].'/admin/admin.html';
//$config['template.admin'] = $config['template.public'] = $config['system.template.path'].'/default/main.html';












