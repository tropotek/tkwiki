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


// -- AUTH CONFIG --
// \Tk\Auth\Adapter\DbTable
$config['system.auth.dbtable.tableName'] = 'user';
$config['system.auth.dbtable.usernameColumn'] = 'username';
$config['system.auth.dbtable.passwordColumn'] = 'password';
$config['system.auth.dbtable.activeColumn'] = 'active';

$config['system.auth.adapters'] = array(
    //'Trap' => '\Tk\Auth\Adapter\Trapdoor',
    'DbTable' => '\Tk\Auth\Adapter\DbTable',
    //'LDAP' => '\Tk\Auth\Adapter\Ldap'     // TODO: Need to create a user if this method is used...
);

// Timezone config
$config['date.timezone'] = 'Australia/Victoria';







// To avoid var dump errors when debug lib not present
// TODO: there could be a better way to handle this in the future 
if (!class_exists('\Tk\Vd')) {
    function vd() {}
    function vdd() {}
}