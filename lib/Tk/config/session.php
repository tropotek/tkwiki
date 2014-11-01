<?php
/**
 * @package config
 * @prefix tk.session
 */


/**
 * tk.session.driver
 * session driver name.
 * Options: native, database, cookie, cache
 */
$config['driver'] = 'native';

/**
 * tk.session.storage
 * session storage parameter, used by drivers.
 */
$config['storage'] = '';

/**
 * tk.session.name
 * session name.
 * It must contain only alphanumeric characters and underscores. At least one letter must be present.
 */
//$config['name'] = 'sn_' . md5(Tk_Config::get('system.sitePath'));
$config['name'] = 'sn_' . substr(md5(Tk_Config::get('system.sitePath')), 0, 8);

/**
 * tk.session.validate
 * session parameters to validate: user_agent, ip_address, expiration.
 */
$config['validate'] = array('ip_address');

/**
 * tk.session.encryption
 * Enable or disable session encryption.
 * Note: this has no effect on the native session driver.
 * Note: the cookie driver always encrypts session data. Set to TRUE for stronger encryption.
 */
$config['encryption'] = false;

/**
 * tk.session.expiration
 * session lifetime. Number of seconds that each session will last.
 * A value of 0 will keep the session active until the browser is closed (with a limit of 24h).
 */
$config['expiration'] = 2880;

/**
 * tk.session.regenerate
 * Number of page loads before the session id is regenerated.
 * A value of 0 will disable automatic session id regeneration.
 */
$config['regenerate'] = 0;

/**
 * tk.session.gc_probability
 * Percentage probability that the gc (garbage collection) routine is started.
 * NOTE: Set to 0 for debian/ubuntu let cron clean up session
 */
$config['gc_probability'] = 0;

/**
 * tk.session.database.group
 * Driver: Database
 * This is the database settings group.
 */
$config['database.group'] = 'default';

/**
 * tk.session.database.table
 * Driver: Database
 * This is the session table name
 */
$config['database.table'] = 'session';


