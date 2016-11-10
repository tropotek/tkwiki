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
include_once(dirname(__FILE__) . '/routes.php');

/*
 * Change the system timezone
 */
//$config['date.timezone'] = 'Australia/Victoria';


/*
 * If you use sub folders in your URL's you 
 * must define the site root paths manually.
 */
//$config['site.path'] = dirname(dirname(dirname(__FILE__)));
//$config['site.url'] = dirname($_SERVER['PHP_SELF']);




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





