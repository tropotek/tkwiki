<?php

//$sitePath = dirname(dirname(dirname(__FILE__)));

$config = \App\Config::getInstance();
$db = $config->getDb();


// Only run the upgrade if needed
if (!$db->hasTable('del')) {
    \Tk\Util\SqlBackup::create($db)->restore(dirname(__FILE__).'/.up-0001.sql');
}


$data->save();
