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



/*
 * Set the error page template
 */
$config['template.error']           = $config['system.template.path']   . '/theme-cube/error.html';

/*
 * Set the maintenance page template
 */
$config['template.maintenance']     = $config['system.template.path']   . '/theme-cube/maintenance.html';


/*
 * Setup what paths to check when migrating SQL
 */
$config['sql.migrate.list'] = array(
    'App Sql' => $config->getSrcPath() . '/config',
    'Lib Sql' => $config->getVendorPath() . '/ttek/tk-base',
    'Plugin Sql' => $config->getPluginPath()
);









