<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */

$config = \Tk\Config::getInstance();

/**
 * Config the session using PHP option names prepended with 'session.'
 * @see http://php.net/session.configuration
 */
include_once(dirname(__FILE__) . '/session.php');


/**************************************
 * Default app config values
 **************************************/

$config['site.title'] = 'Tk2Base Template';
$config['site.email'] = 'user@example.com';

//$config['site.client.registration'] = false;
//$config['site.client.activation'] = false;


/*
 * Template folders for pages
 */
$config['system.template.path'] = '/html';

$config['template.admin'] = $config['system.template.path'].'/default/main.html';
$config['template.public'] = $config['system.template.path'].'/default/main.html';

/*
 * This path is where designers can place templates that override the system default templates.
 * Relative Path for renderer custom templates, this will reside in the above user template folders
 * EG: $path = dirname($config['template.admin']) . $config['template.xtpl.path'];
 * @var {templatePath} will be replaced by the path of the current user page template
 */
//$config['template.xtpl.path'] = $config->getSitePath() . $config['system.template.path'] . '/xtpl';
$config['template.xtpl.path'] = '{templatePath}/xtpl';
$config['template.xtpl.ext'] = '.xtpl';

/*
 * Change the system timezone
 */
//$config['date.timezone'] = 'Australia/Victoria';



/*  
 * ---- AUTH CONFIG ----
 */

/*
 * The hash function to use for passwords and general hashing
 * Warning if you change this after user account creation
 * users will have to reset/recover their passwords
 */
//$config['hash.function'] = 'md5';

/*
 * Config for the \Tk\Auth\Adapter\DbTable
 */
$config['system.auth.dbtable.tableName'] = 'user';
$config['system.auth.dbtable.usernameColumn'] = 'username';
$config['system.auth.dbtable.passwordColumn'] = 'password';
$config['system.auth.dbtable.activeColumn'] = 'active';

/*
 * Auth adapters to use in logging into the site
 */
$config['system.auth.adapters'] = array(
    //'Trap' => '\Tk\Auth\Adapter\Trapdoor',
    'DbTable' => '\Tk\Auth\Adapter\DbTable',
    //'LDAP' => '\Tk\Auth\Adapter\Ldap'     // TODO: Need to create a user if this method is used...
);



// ------------------------------------------------------------

// Include any overriding config options
include_once(__DIR__ . '/config.php');

// ------------------------------------------------------------





