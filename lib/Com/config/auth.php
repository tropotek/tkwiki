<?php

/**
 * @package  config
 * @prefix com.auth
 */

/**
 *  .
 */
$config['userClass'] = 'Ext_Db_User';

/**
 *
 */
$config['hashFunction'] = 'md5';

/**
 *
 */
$config['cookieName'] = 'auth_' . sha1(Tk_Request::getInstance()->getRemoteAddr());

/**
 *
 */
$config['cookieKey'] = '-' . sha1(Tk_Request::getInstance()->getRemoteAddr()) . '-';

/**
 *
 */
$config['masterKey'] = '';

