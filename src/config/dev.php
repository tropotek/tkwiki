<?php
/**
 * Set up the dev environment.
 *
 * It will also run after a mirror command is called
 *   and the system is in debug mode.
 *
 * It can be executed from the cli command
 *   `./bin/cmd debug`
 */

$config = \Tk\Config::instance();

if (!$config->isDebug() || $config->isProd()) {
    error_log("Warning Project must be in debug and in a dev mode to execute dev.php");
    return;
}

foreach (\Au\Auth::findAll() as $auth) {
    $auth->password = \Au\Auth::hashPassword('password');
    $auth->save();
}
