<?php
/**
 * application configuration parameters
 */
use Tk\Config;

return function (Config $config) {

    /**
     * Set environment type to prevent destructive functions on production sites
     * options are 'dev' | 'prod'
     */
    $config['env.type'] = 'prod';

    /**
     * Enable to view more verbose log messages
     */
    $config['debug'] = false;

    /**
     * Site template paths
     */
    $config->set('path.template.public', '/html/default.html');
    $config->set('path.template.admin', $config->get('path.template.public'));
    $config->set('path.template.user', $config->get('path.template.public'));

    /**
     * Setup available page templates that reside in the "/html" directory
     * Add the filename without an extension, the extension should be '.html'
     * array:
     *    ['Template Name' => 'filename']
     */
    $config['wiki.templates'] = [
        'Default' => 'default',
        'Default Fluid' => 'default_fluid',
    ];

    /**
     * Email template relative path
     */
    $config['system.mail.template'] = '/html/templates/mail.default.xtpl';

    /**
     * Enable/disable users to change password from their profile page
     * default: true
     */
    $config['user.profile.password'] = false;

    /**
     * Enable DB sessions
     * Used for the tail log page
     */
    $config['session.db_enable'] = true;

    /**
     * Set the site timezone for PHP and MySQL
     */
    $config['php.date.timezone'] = 'Australia/Melbourne';

    /**
     * Set default home page urls for users
     * (None required for the wiki)
     */
    $config['user.homepage'] = [];

    /**
     * The default log level
     */
    $config['log.logLevel'] = \Psr\Log\LogLevel::ERROR;

};
