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


    /**
     * Whitelist URLS:
     *   - https://domain.com/_ssi  <- main oauth uri
     *   - https://domain.com/login
     *   - https://domain.com/logout
     *   - https://domain.com/
     */

    /**
     * Microsoft external SSI options
     *
     * - Login to https://portal.azure.com go to App Registrations (or create New)
     * - Get the Client ID from tha "Overview" page
     * - Click the "Authentication" page
     * - Check the "ID Tokens" and fill out the valid redirect uris and logout uri
     * - Click the "Certificated & secrets" Create a new Client Secret and not the secret "Value"
     */
    $config['auth.microsoft.enabled']         = false;
    $config['auth.microsoft.createUser']      = false;
    $config['auth.microsoft.userType']        = \App\Db\User::TYPE_MEMBER;
    $config['auth.microsoft.scope']           = 'User.Read';
    $config['auth.microsoft.endpointLogout']  = 'https://login.microsoftonline.com/common/oauth2/v2.0/logout';
    $config['auth.microsoft.endpointToken']   = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    $config['auth.microsoft.endpointScope']   = 'https://graph.microsoft.com/v1.0/me';
    $config['auth.microsoft.emailIdentifier'] = 'userPrincipalName';
    // user defined settings
    $config['auth.microsoft.clientId']        = '';  // define in site /config.php
    $config['auth.microsoft.clientSecret']    = '';  // define in site /config.php

    /**
     * Google external SSI options
     *
     * - Login to https://console.developers.google.com/
     * - Select the "Credentials" page
     * - Create a new "OAuth 2.0" Client ID (setup the OAuth Consent page if redirected)
     */
    $config['auth.google.enabled']         = false;
    $config['auth.google.createUser']      = false;
    $config['auth.google.userType']        = \App\Db\User::TYPE_MEMBER;
    $config['auth.google.scope']           = 'https://www.googleapis.com/auth/userinfo.email';
    $config['auth.google.endpointLogout']  = 'https://www.google.com/accounts/Logout';
    $config['auth.google.endpointToken']   = 'https://www.googleapis.com/oauth2/v4/token';
    $config['auth.google.endpointScope']   = 'https://www.googleapis.com/oauth2/v2/userinfo?fields=name,email,gender,id,picture,verified_email';
    $config['auth.google.emailIdentifier'] = 'email';
    // user defined settings
    $config['auth.google.clientId']        = '';  // define in site /config.php
    $config['auth.google.clientSecret']    = '';  // define in site /config.php

    /**
     * Facebook external SSI options
     *
     * Researching:
     * @see https://codeshack.io/implement-facebook-login-php/
     * @see https://www.cloudways.com/blog/add-facebook-login-in-php/
     *
     * @todo
     */
    $config['auth.facebook.enabled']         = false;
    $config['auth.facebook.createUser']      = false;
    $config['auth.facebook.userType']        = \App\Db\User::TYPE_MEMBER;
    $config['auth.facebook.scope']           = 'https://www.facebook.com/auth/userinfo.email';
    $config['auth.facebook.endpointToken']   = 'https://www.facebook.com/oauth2/v4/token';
    $config['auth.facebook.endpointScope']   = 'https://www.facebook.com/oauth2/v2/userinfo?fields=name,email,gender,id,picture,verified_email';
    $config['auth.facebook.emailIdentifier'] = 'email';
    // user defined settings
    $config['auth.facebook.clientId']        = '';  // define in site /config.php
    $config['auth.facebook.clientSecret']    = '';  // define in site /config.php


};
