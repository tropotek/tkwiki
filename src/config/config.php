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
    $config['path.template.public'] = '/html/default.html';
    $config['path.template.admin']  = $config['path.template.public'];
    $config['path.template.user']   = $config['path.template.public'];

    /**
     * Setup available page templates that reside in the "/html" directory
     * Add the filename without an extension, the extension should be '.html'
     * array:
     *    ['Template Name' => 'filename']
     */
    $config['wiki.templates'] = [
        'Default' => $config['path.template.public'],
        'Default Fluid' => '/html/default_fluid.html',
    ];

    /**
     * Email template relative path
     */
    $config['system.mail.template'] = '/html/templates/mail.default.html';

    /**
     * Enable DB sessions
     */
    $config['session.db_enable'] = true;

    /**
     * Set the site timezone for PHP and MySQL
     */
    $config['php.date.timezone'] = 'Australia/Melbourne';

    /**
     * The default log level
     */
    $config['log.logLevel'] = \Psr\Log\LogLevel::ERROR;

    /**
     * Set default home page urls for users
     * (None required for the wiki)
     */
    $config['user.homepage'] = [];

    /**
     * Can users update their password from their profile page
     * (default: false)
     */
    $config['auth.profile.password'] = false;

    /**
     * Can users register an account
     * (default: false)
     */
    $config['auth.registration.enable'] = false;

    /**
     * Validate user passwords on input
     * - Must include at least one number
     * - Must include at least one letter
     * - Must include at least one capital
     * - Must include at least one symbol
     * - must >= 8 characters
     *
     * Note: validation disabled in dev environments
     * (default: true)
     */
    //$config['auth.password.strict'] = false;

};
