<?php

//$sitePath = dirname(dirname(dirname(__FILE__)));

$config = \App\Config::getInstance();
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

