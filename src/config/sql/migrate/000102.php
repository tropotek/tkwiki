<?php
/**
 * In this update we will be re-encoding all existing encoded DB columns
 * to use the new SSL encoding method.
 *
 * In order to do this we have to decode existing data using the
 * deprecated non-secure encoding and re-encode it using the new SSL encoding.
 *
 * In your `config.php` file you will need to copy the existing $config['system.encrypt']
 * to $config['system.encrypt.old'] and set $config['system.encrypt'] to the new SSL key.
 *
 * To create a new key you can run:
 * ```
 *  $ php -r "echo hash('sha256', 'your_string_to_hash').PHP_EOL;"
 * ```
 *
 * WARNING: Be sure to backup your database before running this update.
 *          YOU HAVE BEEN WARNED.
 *
 */

use Tk\Config;
use Tk\Db;
use Tk\Encrypt;

$sql = "
ALTER TABLE secret MODIFY url TEXT DEFAULT '' NOT NULL;
ALTER TABLE secret MODIFY username TEXT DEFAULT '' NOT NULL;
ALTER TABLE secret MODIFY password TEXT DEFAULT '' NOT NULL;
ALTER TABLE secret MODIFY otp TEXT DEFAULT '' NOT NULL;";
Db::execute($sql);

// This should not be needed anymore, but we keep it here for reference.
//$rows = DB::query('SELECT * FROM secret');
//
//$oldEnc = new Encrypt(Config::getValue('system.encrypt.old'));
//$newEnc = new Encrypt(Config::getValue('system.encrypt'));
//foreach ($rows as $row) {
//    $row->url = $oldEnc->basicDecrypt($row->url);
//    $row->username = $oldEnc->basicDecrypt($row->username);
//    $row->password = $oldEnc->basicDecrypt($row->password);
//    $row->otp = $oldEnc->basicDecrypt($row->otp);
//    $row->keys = $oldEnc->basicDecrypt($row->keys);
//    $row->notes = $oldEnc->basicDecrypt($row->notes);
//
//    $row->url = $newEnc->safeEncrypt($row->url);
//    $row->username = $newEnc->safeEncrypt($row->username);
//    $row->password = $newEnc->safeEncrypt($row->password);
//    $row->otp = $newEnc->safeEncrypt($row->otp);
//    $row->keys = $newEnc->safeEncrypt($row->keys);
//    $row->notes = $newEnc->safeEncrypt($row->notes);
//
//    DB::update('secret', 'secret_id', $row);
//}






