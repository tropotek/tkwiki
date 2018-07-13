<?php

//$sitePath = dirname(dirname(dirname(__FILE__)));

$config = \App\Config::getInstance();
$db = $config->getDb();

// Only run the upgrade if needed
$info = $db->getTableInfo('user');
if (!array_key_exists('del', $info)) {
    $r = \Tk\Util\SqlBackup::create($db)->restore(dirname(__FILE__).'/'.$db->getDriver().'/.up-0001.sql');
}


