<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/*
 *        ---- DO NOT EDIT! ----
 * This is the main include file for the TkLib
 * Do not edit this unless you know what you are doing.
 * There should be no need to modify this file as it
 * only sets up the default environment.
 */

/*
 * setup the default PHP Environment.
 *  o add new functions
 *  o remove magic quotes, etc
 */
include_once (dirname(__FILE__) . "/Functions/php.php");

/*
 * Setup Tk Exceptions
 * See docs
 */
include_once (dirname(__FILE__) . "/Exception.php");

/*
 *  This is the place to include any 3rd party libs to the Tk system
 *  The auto loader will look for the classname (array key)
 *  then load the file given in the array value.
 */
include_once (dirname(__FILE__) . "/Autoloader.php");


/* add htmlMimeMail5 classes */
Tk_AutoLoader::addClass('htmlMimeMail5', dirname(__FILE__) . '/Other/htmlMimeMail5/htmlMimeMail5.php');
Tk_AutoLoader::addClass('stringAttachment', dirname(__FILE__) . '/Other/htmlMimeMail5/htmlMimeMail5.php');
Tk_AutoLoader::addClass('Tk_Loader_StringPropertyMap', dirname(__FILE__) . '/Loader/PropertyMap.php');
Tk_AutoLoader::addClass('Tk_Loader_EncryptStringPropertyMap', dirname(__FILE__) . '/Loader/PropertyMap.php');
Tk_AutoLoader::addClass('Tk_Loader_IntegerPropertyMap', dirname(__FILE__) . '/Loader/PropertyMap.php');
Tk_AutoLoader::addClass('Tk_Loader_FloatPropertyMap', dirname(__FILE__) . '/Loader/PropertyMap.php');
Tk_AutoLoader::addClass('Tk_Loader_BooleanPropertyMap', dirname(__FILE__) . '/Loader/PropertyMap.php');


/*
 * Set config default values
 * The global config object contains site setup variables
 * the defaults are setup here, they can be overridden in
 * your config.ini file.
 *
 */
$server = 'localhost';
if (isset($_SERVER['HTTP_HOST'])) {
    $server = str_replace('www.', '', $_SERVER['HTTP_HOST']);
}

/* $htdocRoot
 *  This should be set to the htdoc root of the site
 *  Eg: /~user/site
 *  No trailing slash!
 */
if (empty($htdocRoot)) {
    $htdocRoot = dirname('/index.html');
    if (isset($_SERVER['PHP_SELF'])) {
        $htdocRoot = dirname($_SERVER['PHP_SELF']);
    }
}

/* $rootPath
 *  This is the filesystem root path of the site
 *  Eg: /home/user/public_html/site
 *  No trailing slash!
 */
if (!isset($sitePath)) {
    $sitePath = dirname(dirname(dirname(__FILE__)));
}

/* $config
 *  Create the global Config object if not created allready
 */
if (!Tk_Config::$instance) {
    Tk_Config::getInstance();
}
Tk_Config::setDebugMode(true);

/* $request
 *  Create the global Request object if not created allready
 *  This object replaces the $_REQUEST, $_POST, $_GET vars
 */
if (!Tk_Request::$instance) {
    Tk_Request::getInstance();
}


/* $response
 *  Create the global Response object if not created allready
 *  This is the buffer to write output to.
 */
if (!Tk_Response::$instance) {
    Tk_Response::getInstance();
}

/*
 * Set the Site's root htdoc path.
 * Eg: /~user/project
 */
Tk_Config::setHtdocRoot($htdocRoot);
/*
 * Set the site's root path
 * Eg: /home/user/public_html/project
 */
Tk_Config::setSitePath($sitePath);
/*
 * Set the site's root path
 * Eg: /home/user/public_html/project
 */
Tk_Config::setLibPath($libPath);
/*
 * This directory must be writable by the httpd server
 */
Tk_Config::setDataPath($sitePath . '/data');
/*
 * This is the default URL path of the data directory
 */
Tk_Config::setDataUrl($htdocRoot . '/data');

/*
 * Timezone settings
 */
Tk_Config::setTimezone('Australia/Queensland');

/*
 * Currency, See the Money object for available currencies
 */
Tk_Config::setCurrency('AUD');

/*
 * Set the default opensource mode
 */
Tk_Config::setOpenSource(false);

/*
 * The email to where all emails are sent
 * when in Debug mode.
 */
Tk_Config::setDebugEmail('info@' . $server);

/*
 * All emails sent to this address are aimed at the
 * developer of the site. (errors, support, etc)
 * Dissabled by default (no email)
 */
Tk_Config::setSupportEmail('');

/*
 * Set the default error log path to the data log
 */
//Tk_Config::setErrorLog(Tk_Config::getDataPath() . '/error.log');

/*
 * Set the default site title
 */
Tk_Config::setSiteTitle('Untitled TK WIKI');

/*
 * Set the email log level
 * If a log is less than or equal to this level an email will be
 * sent to the account in `system.supportEmail`
 */
Tk_Config::set('system.emailLogLevel', Tk::LOG_DISABLED);

/*
 * Set this to false if ssl is not available
 *
 */
Tk_Config::set('system.enableSsl', false);


