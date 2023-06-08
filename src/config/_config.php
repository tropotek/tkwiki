<?php
/**
 * Setup system configuration parameters
 */
use Tk\Config;

return function (Config $config) {

    // Default System DB
    $config['db.default.type'] = 'mysql';
    $config['db.default.host'] = 'localhost';
    $config['db.default.port'] = '3306';
    $config['db.default.name'] = 'dev_tk8wiki';
    $config['db.default.user'] = 'dev';
    $config['db.default.pass'] = 'dev007';

    /*
     * Encrypt function secret seed
     */
    $config['system.mail.template'] = '/html/templates/mail.default.xtpl';

    /*
     * Encrypt function secret seed
     * Change this on your production systems
     */
    $config['system.encrypt'] = '3c54a0c2acdd4f3bc0b280c7097f6d1b';

    /*
     * DB secret API key
     * Use this  key for the mirror command in a dev environment.
     * Keep this key secret. Access to the sites DB can be gained with it.
     */
    $config['db.mirror.secret'] = 'a2ee2caddc146cfed0C0da12f133c726';

    /**
     * Enable DB sessions
     */
    $config['session.db_enable'] = true;

    /**
     * Set the site timezone for PHP and MySQL
     */
    $config['php.date.timezone'] = 'Australia/Melbourne';


    $config['log.logLevel'] = \Psr\Log\LogLevel::ERROR;


    // Setup dev environment
    $config['debug'] = true;
    if ($config->isDebug()) {
        error_reporting(-1);
        $config['php.display_errors'] = 'On';
        $config['php.error_log'] = '/home/godar/log/error.log';
        $config['log.logLevel'] = \Psr\Log\LogLevel::DEBUG;
        $config['system.debug.email'] = 'godar@dev.ttek.org';

        // Used for the Mirror command
        $config['db.mirror.url'] = 'https://godar.ttek.org/Projects/tk8wiki/util/mirror';
    }

};