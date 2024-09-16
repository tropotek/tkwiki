<?php

$registry = \App\Factory::instance()->getRegistry();
$registry->set('site.name', 'Tropotek - Wiki');
$registry->set('site.name.short', 'Wiki');
$registry->set('site.email', 'site@email.com');
$registry->set('site.email.sig', '');
$registry->set('system.maintenance.enabled', '');
$registry->set('system.maintenance.message', '');
$registry->set('system.global.css', '');
$registry->set('system.global.js', '');
$registry->set('system.meta.description', '');
$registry->set('system.meta.keywords', '');
$registry->set('site.account.registration', '');

$registry->set('site.page.header.hide', '');
$registry->set('wiki.page.home', '1');
$registry->set('wiki.enable.credential.mod', '0');

$registry->save();


