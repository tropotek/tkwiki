<?php
use Bs\Registry;

Registry::setValue('site.name', 'Tropotek Wiki');
Registry::setValue('site.name.short', 'Wiki');
Registry::setValue('site.email', 'site@email.com');
Registry::setValue('site.email.sig', '');
Registry::setValue('system.maintenance.enabled', '');
Registry::setValue('system.maintenance.message', '');
Registry::setValue('system.global.css', '');
Registry::setValue('system.global.js', '');
Registry::setValue('system.meta.description', '');
Registry::setValue('system.meta.keywords', '');
Registry::setValue('site.account.registration', '');

Registry::setValue('site.page.header.hide', '');
Registry::setValue('wiki.page.home', '1');
Registry::setValue('wiki.enable.credential.mod', '0');
Registry::setValue('wiki.default.template', '/html/default.html');

Registry::instance()->save();


