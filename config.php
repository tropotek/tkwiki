<?php
/**
 * Setup system configuration parameters
 * @author Tropotek <http://www.tropotek.com/>
 */
use Tk\Config;

/**
 * TODO: New User config example
 * Propose simplifying the config and placing it in the root folder
 * Only show minimal options...
 *
 * The old config should be for site config option that are overridable
 * but not something the user needs to be concerned about
 *
 * - Need to update the \Tk\ConfigLoader to support this
 * - remove config.php.in from old loc to here
 * - Update composer install script to modify new config file
 *
 *
 * @todo Not used at this point in time
 */
return function (Config $config) {

    /**
     * System DB DSN
     * hostname[:port]/username/password/dbname
     */
    $config['db.mysql'] = 'localhost/dev/dev007/dev_wiki';

    /**
     * System string encrypt secret
     * Do not change this after system installed as data may become unreadable
     */
    $config['system.encrypt'] = 'fabf8e9d110a341d469a2fc775a05459'; // LIVE
    //$config['system.encrypt'] = '3c57a0c1acdd6f4bc0b183c70974f31b';   // DEV

    /**
     * Set the site timezone for PHP and MySQL
     */
    $config['php.date.timezone'] = 'Australia/Melbourne';






    /**
     * Set environment to prevent external processes:
     * prod: allows all normal site functions
     * dev: sends all emails to `system.debug.email` and updates DB with dev data on migrate
     * @options dev|prod
     */
    //$config['env.type'] = 'prod';     // todo: should be set in the app config
    $config['env.type'] = 'dev';

    /**
     * Enable to view more verbose log messages
     */
    //$config['debug'] = false;     // todo: should be set in the app config
    $config['debug'] = true;




    $config['env.type'] = 'dev';
    $config['debug'] = true;
    // Setup dev environment
    if ($config->isDev()) {
        // Send all emails to the debug address
        $config['system.debug.email'] = 'godar@dev.ttek.org';
        // DB mirror src site
        $config['db.mirror.url'] = 'https://wiki.ttek.org/util/mirror';
    }

    // setup debug log levels
    if ($config->isDebug()) {
        error_reporting(-1);
        $config['php.display_errors'] = 'On';
        $config['php.error_log'] = '/home/godar/log/error.log';
        $config['log.logLevel'] = \Psr\Log\LogLevel::DEBUG;
        // force logging with NO_LOG links
        //$config['log.enableNoLog'] = false;
    }

};
