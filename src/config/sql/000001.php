<?php

//$sitePath = dirname(dirname(dirname(__FILE__)));

$config = \Tk\Config::getInstance();
$db = $config->getDb();

$data = \Tk\Db\Data::create();
$data->set('site.title', 'Tk WIKI II');
$data->set('site.email', 'user@example.com');
//$data->set('site.client.registration', 'site.client.registration');
//$data->set('site.client.activation', 'site.client.activation');

$data->set('site.meta.keywords', '');
$data->set('site.meta.description', '');
$data->set('site.global.js', '');
$data->set('site.global.css', '');
$data->set('wiki.page.default', 'Home');
$data->set('wiki.page.home.lock', 'wiki.page.home.lock');

$data->save();


////////////////////////////////////////////////////////////////////////////
// Default User Permissions
////////////////////////////////////////////////////////////////////////////


// Save default UserGroup objects
//$list = \App\Db\UserGroupMap::create()->findAll();
///** @var \App\Db\UserGroup $role */
//foreach ($list as $role) {
//    $data = $role->getPermissionData();
//    $constList = \App\Db\UserPermission::getPermissionConstants();
//    foreach ($constList as $k => $v) {
//        // Set all to true for now.
//        $role->getPermissionData()->set($v, $v);
//    }
//    $role->save();
//}




////////////////////////////////////////////////////////////////////////////
// Import all mail logs and
//   convert to message files
////////////////////////////////////////////////////////////////////////////


